<?php
/**
 *
 * @package phpBB Extension - Mafiascum Miscellaneous
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\miscellaneous\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Event listener
 */

class main_listener implements EventSubscriberInterface
{
    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\db\driver\driver */
	protected $db;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\user_loader */
    protected $user_loader;

    /* phpbb\language\language */
    protected $language;

    /* @var \phpbb\auth\auth */
    protected $auth;

    /* @var \phpbb\template\template */
    protected $template;

    protected $hidden_topics;

    protected $controller_helper;

    protected $current_search_id = null;

    protected $hidden_cache = array();

    static public function getSubscribedEvents()
    {
        return array(
			'core.user_setup'  => 'load_language_on_setup',
            'core.index_modify_birthdays_list' => 'generate_scumday_template',
			'core.index_modify_birthdays_sql' => 'limit_birthdays',
			'core.viewtopic_cache_user_data' => 'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row' => 'viewtopic_modify_post_row',
			'core.memberlist_prepare_profile_data' => 'memberlist_prepare_profile_data',
			'core.search_modify_param_after'   => 'capture_search_id',
			'core.search_backend_search_after' => 'filter_search_hidden_topics',
			'core.viewforum_get_topic_ids_data' => 'filter_viewforum_topic_ids',
			'core.viewforum_get_announcement_topic_ids_data' => 'filter_viewforum_announcement_ids',
			'core.feed_base_modify_item_sql' => 'filter_feed_hidden_topics',
			'core.viewtopic_modify_page_title' => 'viewtopic_assign_hide_vars',
        );
    }

	function capture_search_id($event)
	{
		$this->current_search_id = (string) $event['search_id'];
	}

	protected function get_hidden_cached($scope)
	{
		if (empty($this->user->data['user_id']) || $this->user->data['user_id'] == ANONYMOUS)
		{
			return array();
		}
		if (!isset($this->hidden_cache[$scope]))
		{
			$this->hidden_cache[$scope] = $this->hidden_topics->get_hidden_topic_ids(
				(int) $this->user->data['user_id'],
				$scope
			);
		}
		return $this->hidden_cache[$scope];
	}
    public function __construct( \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db,  \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth, \phpbb\template\template $template, \mafiascum\miscellaneous\hidden\manager $hidden_topics, \phpbb\controller\helper $controller_helper)
    {
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
		$this->auth = $auth;
		$this->template = $template;
		$this->hidden_topics = $hidden_topics;
		$this->controller_helper = $controller_helper;
    }

	function filter_search_hidden_topics($event)
	{
		if (empty($this->user->data['user_id']) || $this->user->data['user_id'] == ANONYMOUS)
		{
			return;
		}

		$hidden = $this->get_hidden_cached(\mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE);
		if ($this->current_search_id === 'egosearch')
		{
			$hidden = array_unique(array_merge(
				$hidden,
				$this->get_hidden_cached(\mafiascum\miscellaneous\hidden\manager::SCOPE_EGOSEARCH)
			));
		}
		if (empty($hidden))
		{
			return;
		}

		$id_ary = $event['id_ary'];
		if (empty($id_ary))
		{
			return;
		}

		$show_results = $event['show_results'];
		$total = (int) $event['total_match_count'];

		if ($show_results === 'topics')
		{
			$hidden_flip = array_flip($hidden);
			$filtered = array();
			foreach ($id_ary as $tid)
			{
				if (!isset($hidden_flip[(int) $tid]))
				{
					$filtered[] = $tid;
				}
			}
			$removed = count($id_ary) - count($filtered);
			$event['id_ary'] = $filtered;
			$event['total_match_count'] = max(0, $total - $removed);
			return;
		}

		$sql = 'SELECT post_id, topic_id FROM ' . POSTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('post_id', array_map('intval', $id_ary));
		$result = $this->db->sql_query($sql);
		$post_topic = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$post_topic[(int) $row['post_id']] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		$hidden_flip = array_flip($hidden);
		$filtered = array();
		foreach ($id_ary as $pid)
		{
			$pid_int = (int) $pid;
			$tid = isset($post_topic[$pid_int]) ? $post_topic[$pid_int] : null;
			if ($tid === null || !isset($hidden_flip[$tid]))
			{
				$filtered[] = $pid;
			}
		}
		$removed = count($id_ary) - count($filtered);
		$event['id_ary'] = $filtered;
		$event['total_match_count'] = max(0, $total - $removed);
	}

	function filter_viewforum_topic_ids($event)
	{
		$hidden = $this->get_hidden_cached(\mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE);
		if (empty($hidden))
		{
			return;
		}
		$sql_ary = $event['sql_ary'];
		$sql_ary['WHERE'] .= ' AND ' . $this->db->sql_in_set('t.topic_id', array_map('intval', $hidden), true);
		$event['sql_ary'] = $sql_ary;
	}

	function filter_viewforum_announcement_ids($event)
	{
		$hidden = $this->get_hidden_cached(\mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE);
		if (empty($hidden))
		{
			return;
		}
		$sql_ary = $event['sql_ary'];
		$sql_ary['WHERE'] = '(' . $sql_ary['WHERE'] . ') AND ' . $this->db->sql_in_set('t.topic_id', array_map('intval', $hidden), true);
		$event['sql_ary'] = $sql_ary;
	}

	function filter_feed_hidden_topics($event)
	{
		$hidden = $this->get_hidden_cached(\mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE);
		if (empty($hidden))
		{
			return;
		}
		$sql_ary = $event['sql_ary'];
		$from = isset($sql_ary['FROM']) ? $sql_ary['FROM'] : array();
		$alias = null;
		if (isset($from[POSTS_TABLE]))
		{
			$alias = $from[POSTS_TABLE];
		}
		else if (isset($from[TOPICS_TABLE]))
		{
			$alias = $from[TOPICS_TABLE];
		}
		if ($alias === null)
		{
			return;
		}
		$col = $alias . '.topic_id';
		$where = isset($sql_ary['WHERE']) ? $sql_ary['WHERE'] : '';
		$exclusion = $this->db->sql_in_set($col, array_map('intval', $hidden), true);
		$sql_ary['WHERE'] = $where === '' ? $exclusion : '(' . $where . ') AND ' . $exclusion;
		$event['sql_ary'] = $sql_ary;
	}

	function viewtopic_assign_hide_vars($event)
	{
		if (empty($this->user->data['user_id']) || $this->user->data['user_id'] == ANONYMOUS)
		{
			return;
		}

		$topic_data = $event['topic_data'];
		$topic_id = isset($topic_data['topic_id']) ? (int) $topic_data['topic_id'] : 0;
		if ($topic_id <= 0)
		{
			return;
		}

		$user_id = (int) $this->user->data['user_id'];
		$hash = generate_link_hash('maf_hide_topic');

		$egosearch = \mafiascum\miscellaneous\hidden\manager::SCOPE_EGOSEARCH;
		$everywhere = \mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE;

		$this->template->assign_vars(array(
			'MAF_TOPIC_EGOSEARCH_HIDDEN' => $this->hidden_topics->is_hidden($user_id, $topic_id, $egosearch),
			'U_MAF_TOPIC_TOGGLE_EGOSEARCH_HIDE' => $this->controller_helper->route(
				'mafiascum_miscellaneous_hidden_topics_toggle',
				array('topic_id' => $topic_id, 'scope' => $egosearch, 'hash' => $hash)
			),
			'MAF_TOPIC_EVERYWHERE_HIDDEN' => $this->hidden_topics->is_hidden($user_id, $topic_id, $everywhere),
			'U_MAF_TOPIC_TOGGLE_EVERYWHERE_HIDE' => $this->controller_helper->route(
				'mafiascum_miscellaneous_hidden_topics_toggle',
				array('topic_id' => $topic_id, 'scope' => $everywhere, 'hash' => $hash)
			),
		));
	}
	function memberlist_prepare_profile_data($event) {
		global $phpEx;

		$template_data = $event['template_data'];
		$user_data = $event['data'];
		
		$user_id = $user_data['user_id'];
		$template_data['U_SEARCH_TOPICS'] = "/search.$phpEx?author_id=$user_id&sr=topics";

		$event['template_data'] = $template_data;
	}
	function viewtopic_modify_post_row($event) {
		$post_row = $event['post_row'];
		$original_signature = $post_row['SIGNATURE'];
		$reformatted_signature = $original_signature;

		$lines = substr_count($original_signature, '<br>') + 1;

		if($lines > 4) {
			$javascript  = "var container = this.parentElement.querySelector('.signature-collapsible');"
			             . "container.classList.toggle('signature-collapsed');"
			             . "this.innerText = container.classList.contains('signature-collapsed') ? 'Show' : 'Hide';"
			             . "return false;";
			$reformatted_signature = '<a href="#" onclick="javascript:' . $javascript . '">Show</a>';
			$reformatted_signature .= '<div class="signature-collapsible signature-collapsed">' . $original_signature . '</div>';
		}

		$post_row['SIGNATURE'] = $reformatted_signature;
		$event['post_row'] = $post_row;
	}
	function viewtopic_cache_user_data($event) {
		$row = $event['row'];
		$user_cache_data = $event['user_cache_data'];

		$user_cache_data['joined'] = $this->user->format_date($row['user_regdate'], 'F j, Y');

		$event['user_cache_data'] = $user_cache_data;
	}
	function add_cake($event) {

		/***
		global $config;
		$now = getdate(time() + $this->user->timezone + $this->user->dst - date('Z'));
		$user_data = $event['user_poster_data'];
		$post_row = $event['post_row'];
		$cake;
		if ($config['allow_birthdays'] && !empty($user_data['user_regdate']))
		{
			$userRegDate = strftime("%d-%m-%Y", $user_data['user_regdate']);
			list($bday_day, $bday_month) = array_map('intval', explode('-', $userRegDate));

			if ($bday_day === (int) $now['mday'] && $bday_month === (int) $now['mon'])
			{
				$cake = $this->getUserScumdayCake(true);			
			} else {
				$cake = false;
			}
		}
		$event['post_row'] = array_merge($event['post_row'],array(
			'USER_SCUMDAYCAKE' => $cake,
		));
		***/
	}
    /**
     * Load the language file
     *
     * @param \phpbb\event\data $event The event object
     */
    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'mafiascum/miscellaneous',
            'lang_set' => 'common',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }
	function getUserScumdayCake ($isBirthday){
		if ($isBirthday){
			return '<img src="' . $this->root_path . 'ext/mafiascum/miscellaneous/images/icon_scumday.png" alt="' . $this->user->lang['VIEWTOPIC_BIRTHDAY'] . '" title="' . $this->user->lang['VIEWTOPIC_BIRTHDAY'] . '"  style="vertical-align:middle;" />';
		}
		return false;
	}
	function limit_birthdays($event){
		$sql = $event['sql_ary'];
		$sql['WHERE'] .= ' AND ADDDATE(from_unixtime(u.user_lastvisit), INTERVAL 1 YEAR) > CURDATE()
						   AND u.user_posts > 41';
		$event['sql_ary'] = $sql;
	}
	function generate_scumday_template($event) {
		global $config;
		$this->language->add_lang('common', 'mafiascum/miscellaneous');
		$scumdays = array();
		if ($config['load_birthdays'] && $config['allow_birthdays'])
		{
			$sql = ' SELECT u.user_id, u.username, u.user_colour, u.user_regdate
				     FROM ' . USERS_TABLE . ' u
				     LEFT JOIN ' . BANLIST_TABLE . ' b ON u.user_id = b.ban_userid
				     WHERE (b.ban_id IS NULL OR b.ban_exclude = 1)
					 AND DATE_FORMAT(NOW(), "%m-%d") = DATE_FORMAT(FROM_UNIXTIME(u.user_regdate), "%m-%d")
					 AND DATE(NOW()) != DATE(FROM_UNIXTIME(u.user_regdate))
					 AND u.user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ') 
					 AND ADDDATE(from_unixtime(u.user_lastvisit), INTERVAL 1 YEAR) > CURDATE() 
					 AND u.user_posts > 41';
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
			foreach ($rows as $row)
			{
				$scumday_username	= get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']);

				$scumdays[] = array(
					'USERNAME'	=> $scumday_username
				);
			}
			$this->template->assign_block_vars_array('scumdays', $scumdays);
		}
	}

}
?>