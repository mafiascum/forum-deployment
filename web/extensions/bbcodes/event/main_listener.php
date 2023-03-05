<?php
/**
 *
 * @package phpBB Extension - Mafiascum BBCodes
 * @copyright (c) 2018 mafiascum.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\bbcodes\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;
use \Datetime;
/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\db\driver\driver */
    protected $db;

    static public function getSubscribedEvents()
    {
        return array(
			'core.text_formatter_s9e_parse_after' => 'text_formatter_s9e_parse_after',
			'core.text_formatter_s9e_render_before' => 'text_formatter_s9e_render_before',
			'core.text_formatter_s9e_render_after' => 'text_formatter_s9e_render_after',
			'core.acp_ranks_save_modify_sql_ary' => 'acp_ranks_save_modify_sql_ary',
			'core.text_formatter_s9e_configure_after' => 'configure_bbcodes',
			'core.decode_message_before' => 'decode_message_before',
			'core.text_formatter_s9e_parser_setup'    => 'onParserSetup',
			'core.posting_modify_quote_attributes' => 'posting_modify_quote_attributes',
        );
    }

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper	$helper		Controller helper object
     * @param \phpbb\template\template	$template	Template object
     * @param \phpbb\request\request	$request	Request object
     */
    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
		$this->db = $db;
	}

	static private function changeTagName($node, $name) {
		$childnodes = array();
		foreach ($node->childNodes as $child){
			$childnodes[] = $child;
		}
		$newnode = $node->ownerDocument->createElement($name);
		foreach ($childnodes as $child){
			$child2 = $node->ownerDocument->importNode($child, true);
			$newnode->appendChild($child2);
		}
		foreach ($node->attributes as $attrName => $attrNode) {
			$attrName = $attrNode->nodeName;
			$attrValue = $attrNode->nodeValue;
			$newnode->setAttribute($attrName, $attrValue);
		}
		$node->parentNode->replaceChild($newnode, $node);
	}

	private function get_post_number($topic_id, $post_id)
	{
		$this->db->sql_query('SET @post_count := -1;');

		$sql = "WITH post_count_data AS(
				SELECT
					post_id,
					@post_count := @post_count + 1 AS post_number
				FROM " . POSTS_TABLE . "
				WHERE topic_id=$topic_id
				ORDER BY post_time ASC
			)
			SELECT post_number
			FROM post_count_data
			WHERE post_id=$post_id";

		$result = $this->db->sql_query($sql);
		$post_number = (int) $this->db->sql_fetchfield('post_number');
		$this->db->sql_freeresult($result);

		return $post_number;
	}

	private function get_topic_id_from_post_id($post_id) {
		$sql = "SELECT topic_id 
				FROM " . POSTS_TABLE . "
				WHERE post_id=$post_id";
		$result = $this->db->sql_query($sql);
		$topic_id = (int) $this->db->sql_fetchfield('topic_id');
		$this->db->sql_freeresult($result);

		return $topic_id;
	}

	public function posting_modify_quote_attributes($event)
	{
		$quote_attributes = $event['quote_attributes'];
		$post_data = $event['post_data'];

		$post_number = $this->get_post_number($post_data['topic_id'], $post_data['post_id']);
		$quote_attributes['post_num'] = $post_number;

		$event['quote_attributes'] = $quote_attributes;
	}

	public function text_formatter_s9e_parse_after($event) {
		global $topic_id;

		$dom = new \DOMDocument;
		$dom->loadXML($event['xml']);
		$xpath = new \DOMXPath($dom);

		// QUOTE
		$result = $xpath->query("//QUOTE[@post_id and not(@post_num)]");
		foreach($result as $countdown) {
			$post_id = $result->item(0)->attributes->getNamedItem('post_id')->value;
			$topic_id = $this->get_topic_id_from_post_id($post_id);
			$post_num = $this->get_post_number($topic_id, $post_id);
			$result->item(0)->setAttribute("post_num", $post_num);
		}

		// COUNTDOWN
		$result = $xpath->query("//COUNTDOWN/text()[normalize-space()]");
		foreach($result as $countdown) {
			$newCountdown = $dom->createTextNode($this->bbcode_countdown($countdown->nodeValue));
			$countdown->parentNode->replaceChild($newCountdown, $countdown);
		}

		// DICE
		$result = $xpath->query("//DICE/text()[normalize-space()]");
		foreach($result as $dice) {
			$newDiceText = preg_replace_callback('/(((\d+)d(\d+)(?:([\+-\/\*])(\d+))?) *(?:= *)?(?:(?:SEEDSTART)? *(\d+) *(?:SEEDEND)?)? *(?:= *Fixed)? *)/',
				function($matches) {
					return $this->bbcode_dice(
						$matches[3] ?? '',
						$matches[4] ?? '',
						$matches[1] ?? '',
						$matches[2] ?? '',
						$matches[5] ?? '',
						$matches[6] ?? '',
						$matches[7] ?? ''
					);
				},
				$dice->nodeValue
			);
			$newDice = $dom->createTextNode($newDiceText);
			$dice->parentNode->replaceChild($newDice, $dice);
		}

		// POST
		// Works a little differently because [post]1[/post] is not parsesd as a bbcode as it does not match the template [post=#{NUMBER}]...
		// we need to find descendents of things that aren't a CODE node
		// then see if there's any non-parsed post tags to mess with
		// if there are, pack in the absolute id, then manually reparse the tag
		// the tag should now parse correctly as it DOES match the template for the tag
		// We only ever have to do work here if we are in a topic, not a PM.
		if ($topic_id) {
			$result = $xpath->query("//*[not(ancestor-or-self::CODE)]/text()[normalize-space()]");
			$root_needs_rich_text_tag = false;
			foreach($result as $textNode) {
				$event['parser']->enable_bbcodes();
				if (preg_match('/(?:\[post\]|\[post=(?!#)(\d+)\])(.*?)\[\/post\]/', $textNode->nodeValue)) {
					$root_needs_rich_text_tag = ($dom->documentElement->tagName == 't');
					$newText = preg_replace_callback(
						'/(?:\[post\]|\[post=(?!#)(\d+)\])(.*?)\[\/post\]/',
						function($matches) {
							return($this->bbcode_post($matches[1], $matches[2]));
						},
						$textNode->nodeValue
					);
					$newDom = new \DOMDocument;
					$newElem = $event['parser']->parse($newText);
					$newDom->loadXML($newElem);
					$newNode = $newDom->documentElement;
					$newNode = $dom->importNode($newNode, true);
					$textNode->parentNode->replaceChild($newNode, $textNode);
				}
			}

			// This has been flagged as not having any bbcode in it. We need to tell it otherwise.
			if ($root_needs_rich_text_tag) {
				self::changeTagName($dom->documentElement, 'r');
			}
		}

		$event['xml'] = $dom->saveXML($dom->documentElement);
	}
	
	public function onParserSetup($event)
	{
		$event['parser']->get_parser()->maxFixingCost = PHP_INT_MAX;
	}

	function configure_bbcodes($event) {

		$bbcodesWithoutLineBreakAfter = array('LIST');

		$event['configurator']->tags['SIZE']->filterChain
		->append(array(__CLASS__, 'filter_size'));

		foreach ($event['configurator']->tags as $tag)
		{
			$tag->nestingLimit = PHP_INT_MAX;
			$tag->tagLimit     = PHP_INT_MAX;
		}
		foreach ($event['configurator']->plugins as $plugin)
		{
			$plugin->setRegexpLimit(PHP_INT_MAX);
		}

		$allTagNames = array();
		foreach($event['configurator']->tags as $tagName => $tag) {
			$allTagNames[] = $tagName;
		}

		foreach ($event['configurator']->tags as $sourceTagName => $tag) {

			foreach($allTagNames as $targetTagName) {
				//if($sourceTagName !== $targetTagName) {
					$tag->rules->allowDescendant($targetTagName);
					$tag->rules->allowChild($targetTagName);
				//}
			}

			if(!in_array($sourceTagName, $bbcodesWithoutLineBreakAfter)) {
				$tag->rules->ignoreSurroundingWhitespace(false);
			}
		}

		// Change hardcoded bbcode templates to be <div> based so they can contain other <div> based tags
		// it's <div>s all the way down now
		$event['configurator']->tags['b']->template = '<div style="display: inline; font-weight: bold"><xsl:apply-templates/></div>';
		$event['configurator']->tags['i']->template = '<div style="display: inline; font-style: italic"><xsl:apply-templates/></div>';
		$event['configurator']->tags['u']->template = '<div style="display: inline; text-decoration: underline"><xsl:apply-templates/></div>';
		$event['configurator']->tags['size']->template = '<div><xsl:attribute name="style"><xsl:text>font-size: </xsl:text><xsl:value-of select="substring(@size, 1, 4)"/><xsl:text>%; line-height: normal; display: inline</xsl:text></xsl:attribute><xsl:apply-templates/></div>';
		//TODO is this the right thing to do?
		//$event['configurator']->tags['color']->template = new UnsafeTemplate($event['configurator']->templateNormalizer->normalizeTemplate('<span style="color: {COLOR}"><xsl:apply-templates/></span>'));
		$event['configurator']->BBCodes->addCustom('[COLOR={COLOR}]{TEXT}[/COLOR]', new UnsafeTemplate($event['configurator']->templateNormalizer->normalizeTemplate('<div style="display: inline; color: {COLOR}"><xsl:apply-templates/></div>')));		
		$event['configurator']->BBCodes->addCustom("[QUOTE
		author={TEXT1;optional}
		post_id={UINT;optional}
		post_num={UINT;optional}
		post_url={URL;optional;postFilter=#false}
		msg_id={UINT;optional}
		msg_url={URL;optional;postFilter=#false}
		profile_url={URL;optional;postFilter=#false}
		time={UINT;optional}
		url={URL;optional}
		user_id={UINT;optional}
		author={PARSE=/^\\[url=(?'url'.*?)](?'author'.*)\\[\\/url]$/i}
		author={PARSE=/^\\[url](?'author'(?'url'.*?))\\[\\/url]$/i}
		author={PARSE=/(?'url'https?:\\/\\/[^[\\]]+)/i}
	]{TEXT2}[/QUOTE]", $event['configurator']->tags['quote']->template);

	}

	static public function filter_size(\s9e\TextFormatter\Parser\Tag $tag) {
		
		$size = intval($tag->getAttribute('size'));
		$min_size = 10;

		if($size < $min_size)
			$tag->setAttribute('size', $min_size);

		return true;
	}

	/**
	 * Parse countdown tag
	 */
	function bbcode_countdown($in)
	{
		global $user, $config;

		$error = true;
		$gmt_offset = "0.00";
		$timestamp = "";

		if(preg_match_all('/((\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})) ?(-?\d{1,2}\.\d{1,2})?/', $in, $matches, PREG_SET_ORDER)) {

			$error = false;

			$timestamp = $matches[0][1];
			$timestamp_unix = gmmktime($matches[0][5], $matches[0][6], $matches[0][7], $matches[0][3], $matches[0][4], $matches[0][2]);
			if(count($matches[0]) > 8) {
				$gmt_offset = $matches[0][8];
			}
			else {
				$offset_seconds = ($user == null || $user->timezone == null) ? 0 : ($user->timezone->getOffset((new DateTime)->setTimestamp($timestamp_unix)));
				$gmt_offset = ((float)$offset_seconds) / 60 / 60;
			}
		}
		else if(preg_match_all('/(\d+(?:\.\d+)?)\s+((?:day)|(?:hour)|(?:minute))s?/', $in, $matches_array, PREG_SET_ORDER)) {

			$offset = 0;
			$index = 0;
			while($index < count($matches_array)) {
				
				$matches = $matches_array[$index];
				
				$value = $matches[ 1 ];
				$unit = $matches[ 2 ];

				if($unit == 'day') {
					$offset += $value*60*60*24;
				}
				else if($unit == 'hour') {
					$offset += $value*60*60;
				}
				else if($unit == 'minute') {
					$offset += $value*60;
				}
				
				++$index;
			}

			$deadline = min(PHP_INT_MAX, gmdate("U") + ((-5 * 3600) + (1 * 3600)) + $offset);
			$gmt_offset = -4;

			$timestamp = gmdate("Y-m-d H:i:s", $deadline);
		}
		$in = trim($timestamp . " " . number_format($gmt_offset, 2));

		return $in;
	}

	function bbcode_post($post_number, $in) {
		global $topic_id, $db;
		$is_post_id = false;
		$error = true;
		
		if (empty($topic_id)) {
			return '[post=' . $post_number . ']' . $in . '[/post]';
		}
		if (!($post_number)) {
			$post_number=$in;
		}
		if($post_number == '') {
			return $in;
		}
		if($post_number[0] == '#') {
			
			$post_number = substr($post_number, 1);
			$is_post_id = true;
		}
		if(!preg_match('/\d+/', $post_number)) {//Must be integer.
			return '[post=' . $post_number . ']' . $in . '[/post]';
		}

		//We need for $post_number to be the internal post id.
		
		if(!$is_post_id) {
			
			$error = false;
			$sql='SET @post_count := -1;';
			$db->sql_query($sql);
			$sql = 'SELECT tmp.post_id, tmp.post_number FROM 
	        ( 
	          SELECT 
	            post_id,
	            @post_count := @post_count + 1 AS post_number
	          FROM ' . POSTS_TABLE . '
	          WHERE topic_id=' . $topic_id . '
	          ORDER BY post_time ASC
	        ) AS tmp';
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result)){
				if($row['post_number'] == $post_number){
					$dbpost_id=$row['post_id'];
				}
				else if ($row['post_number'] == 0){
					$firstpost_id=$row['post_id'];
				}
			}
			if (empty($dbpost_id)){
				$dbpost_id = $firstpost_id;
			}
			$db->sql_freeresult($result);
			$post_number = $dbpost_id;
		}
		return '[post=#' . $post_number . ']' . $in . '[/post]';
	}

	/**
	 * Parse dice tag
	 */
	function bbcode_dice($dice, $sides, $in, $inExcludingSeed, $operator, $operand, $previousSeed)
	{
		global $mode;
		
		// If the seed is encoded in the actual bbcode, allow it to go through without reformatting it as STATIC.
		// The SEEDSTART12345SEEDEND format only appears from the SQL run here from the 3.0.x -> 3.3.x migration:
		//	https://github.com/mafiascum/forum-deployment/blob/main/web/forum/migration/db/data/before.sql
		// We need to allow this in order to prevent non-fixed dice tags from being converted to fixed.

		// The format [dice]4d6 = 941362743 [/dice] is for non-static dice tags from phpBB 2.x

		// Note that we'll only allow this functionality if the reparser is run through via CLI.
		$nonFixedDice = php_sapi_name() == "cli" && ((str_contains($in, 'SEEDSTART') && str_contains($in, 'SEEDEND')) || preg_match('/ *= *\d+ *$/', $in));
		if($dice > 100 || $sides > 500 || $dice <= 0 || $sides <= 0) {
			return $in;
		}
		
		$in = trim($in);
		$error = false;

		if($previousSeed != '') {
			$seed = $previousSeed;
		}
		else {
			mt_srand((double)microtime()*100000, MT_RAND_PHP);
			$seed = mt_rand();
		}
		
		// Determine whether this is a static or normal dice roll.
		if(($previousSeed != '' || $mode == 'edit') && !$nonFixedDice) {
			// Static dice roll(edited post, quoted dice tag, or a seed was specified)
			$seedString = ' ' . $seed;
		}
		else {
			// Normal dice roll.
			$seedString = 'SEEDSTART' . $seed . 'SEEDEND';
		}

		return $inExcludingSeed . $seedString;
	}

	public function text_formatter_s9e_render_after($event) {

		$event['html'] = preg_replace_callback(
			'/<span class="dice-tag-original">(.*?)<\/span>/',
			function($matches) {
				$in = $matches[1];

				if(preg_match_all('/(\d+)d(\d+)(?:([\+-\/\*])(\d+))? ?((:?SEEDSTART)?\d+(:?SEEDEND)?)/', $in, $matches_array, PREG_SET_ORDER)) {
					
					if(empty($matches_array))
						return $in;

					$inner_match = $matches_array[0];

					return $this->bbcode_second_pass_dice(
						$inner_match[1],
						$inner_match[2],
						$inner_match[5],
						$inner_match[3],
						$inner_match[4]
					);
				}

				return $in;
			},
			$event['html']
		);
	}

	function bbcode_second_pass_dice($dice, $sides, $seed, $operator, $operand)
	{
		$total = 0;
		$fixed = False;

		if($seed != '' && $seed[0] == 'S') {

			$seed = preg_replace('/SEEDSTART(\d+)SEEDEND/', '$1', $seed);
		}
		else {

			$fixed = True;
		}

		$sides = (int)$sides;
		$dice = (int)$dice;
		$seed = (int)$seed;

		mt_srand($seed, MT_RAND_PHP);

		$buffer = '<div class="dicebox"><div class="dicetop"><emph>Original Roll String:</emph> ' . $dice . 'd' . $sides . (($operator != '' && $operand) != '' ? ($operator . $operand) : '') . ($fixed==True ? ' <fixed>(STATIC)</fixed>' : '') . '</div>'
		.      '<div class="diceroll"><emph>' . $dice . ' ' . $sides . '-Sided Dice:</emph> (';

		for($diceCounter = 0;$diceCounter < $dice;++$diceCounter) {

			$roll = mt_rand(1, $sides);
			$total += $roll;

			if($diceCounter > 0) {
				
				$buffer .= ', ';
			}

			$buffer .= $roll;
		}

		if($operator == '+')
			$total += $operand;
		else if($operator == '-')
			$total -= $operand;
		else if($operator == '*')
			$total *= $operand;
		else if($operator == '/') {
			if($operand == 0)
				$total = "INVALID";
			else
				$total /= $operand;
		}

		$buffer .= ')' . ($operator != '' && $operand != '' ? ($operator . $operand) : '') . ' = ' . $total . '</div></div>';

		return $buffer;
	}

	public function text_formatter_s9e_render_before($event) {

		global $config;

		//Used by post tag
		$script_path = rtrim($config['script_path'], '/') . '/';
		$event['renderer']->get_renderer()->setParameter("SERVER_PROTOCOL", $config['server_protocol']);
		$event['renderer']->get_renderer()->setParameter("SERVER_NAME", $config['server_name']);
		$event['renderer']->get_renderer()->setParameter("SCRIPT_PATH", $script_path);
		
		$event['xml'] = preg_replace_callback(
			'/<s>\[countdown\]\<\/s\>(.*?)<e>\[\/countdown\]<\/e>/',
			function($matches) {
				$in = $matches[1];

				if(preg_match_all('/(\d{4})-(\d\d)-(\d\d) (\d\d):(\d\d):(\d\d) ?(-?\d{1,2}\.\d{1,2})?/', $in, $matches_array, PREG_SET_ORDER)) {
					
					if(empty($matches_array))
						return $in;

					$inner_match = $matches_array[0];

					$year = $inner_match[1];
					$month = $inner_match[2];
					$day = $inner_match[3];
					$hour = $inner_match[4];
					$minute = $inner_match[5];
					$second = $inner_match[6];
					$timezone = $inner_match[7];

					$displayBuffer = "";
		
					if($timezone == '') {
						$gmt_offset = (-5 * 3600) + (1 * 3600);
					}
					else {
						$gmt_offset = (float)$timezone * 3600;
					}
					
					$timeNow = gmdate("U") + $gmt_offset;
					$timeDeadline = gmmktime($hour, $minute, $second, $month, $day, $year);
					$timeDiff = $timeDeadline - $timeNow;
			
					//The 'deadline' has been reached.
					if( $timeDiff <= 0 ) {
						
						$displayBuffer = "(expired on " . gmstrftime("%Y-%m-%d %H:%M:%S", $timeDeadline) . ")";
					}
					else
					{//There is still time remaining.
						$days    = (int) ($timeDiff / 60 / 60 / 24);
						$hours   = (int) (($timeDiff / 60 / 60) % 24);
						$minutes = (int) (($timeDiff / 60) % 60);
						$seconds = (int) ($timeDiff % 60);
						$displayBuffer	= "$days day"       . ($days    == 1 ? "" : "s") . ", "
								.        "$hours hour"     . ($hours   == 1 ? "" : "s") . ", "
								.        "$minutes minute" . ($minutes == 1 ? "" : "s");
					}

					return $displayBuffer;
				}

				return "[countdown]" . $in . "[/countdown]";
			},
			$event['xml']
		);
	}

	public function acp_ranks_save_modify_sql_ary($event) {
		$sql_ary = $event['sql_ary'];

		$sql_ary['rank_title'] = htmlspecialchars_decode($sql_ary['rank_title']);

		$event['sql_ary'] = $sql_ary;
	}

	public function decode_message_before($event) {
		$event['message_text'] = preg_replace_callback(
			'/<s>\[dice\]\<\/s\>(.*?)<e>\[\/dice\]<\/e>/',
			function($matches) {

				$strip_seed = strcasecmp($this->request->server('REQUEST_METHOD'), 'GET') != 0;

				if($strip_seed === TRUE)
					return '<s>[dice]</s>' . preg_replace('/SEEDSTART(\d+)SEEDEND/', '', $matches[1]) . '<e>[/dice]</e>';
				else
					return '<s>[dice]</s>' . preg_replace('/SEEDSTART(\d+)SEEDEND/', ' $1', $matches[1]) . '<e>[/dice]</e>';
			},
			$event['message_text']
		);
	}
}