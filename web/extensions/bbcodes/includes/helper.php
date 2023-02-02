<?php
/**
 *
 * @package phpBB Extension - Mafiascum BBCodes
 * @copyright (c) 2018 mafiascum.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\bbcodes\includes;

use phpbb\db\driver\factory as database;

class helper
{

	/** @var \phpbb\db\driver\factory */
	protected $db;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var \acp_bbcodes */
	protected $acp_bbcodes;

	/**
	 * Constructor of the helper class.
	 *
	 * @param \phpbb\db\driver\factory		$db
	 * @param string						$root_path
	 * @param string						$php_ext
	 *
	 * @return void
	 */
	public function __construct(database $db, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;

		if (!class_exists('acp_bbcodes'))
		{
			include($this->root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
		}

		$this->acp_bbcodes = new \acp_bbcodes;
	}

	function install_all_bbcodes()
	{
		$this->install_bbcode($this->countdown_bbcode_data());
		$this->install_bbcode($this->post_bbcode_data());
		$this->install_bbcode($this->dice_bbcode_data());
		$this->install_bbcode($this->votecount_bbcode_data());
		$this->install_bbcode($this->votecountbbcode_bbcode_data());
		$this->install_bbcode($this->spoilerequals_bbcode_data());
	}

	/**
	 * Install the new BBCode adding it in the database or updating it if it already exists.
	 *
	 * @return void
	 */
	public function install_bbcode($data)
	{
		// Remove conflicting BBCode
		$this->remove_bbcode($data['bbcode_tag']);

		//$data = $this->bbcode_data();

		if (empty($data))
		{
			return;
		}

		$data['bbcode_id'] = (int) $this->bbcode_id();
		$data = array_replace(
			$data,
			$this->acp_bbcodes->build_regexp(
				$data['bbcode_match'],
				$data['bbcode_tpl']
			)
		);

		// Get old BBCode ID
		$old_bbcode_id = (int) $this->bbcode_exists($data['bbcode_tag']);

		// Update or add BBCode
		if ($old_bbcode_id > NUM_CORE_BBCODES)
		{
			$this->update_bbcode($old_bbcode_id, $data);
		}
		else
		{
			$this->add_bbcode($data);
		}
	}

	function uninstall_all_bbcodes()
	{
		$this->uninstall_bbcode($this->countdown_bbcode_data());
		$this->uninstall_bbcode($this->post_bbcode_data());
		$this->uninstall_bbcode($this->dice_bbcode_data());
		$this->uninstall_bbcode($this->votecount_bbcode_data());
		$this->uninstall_bbcode($this->votecountbbcode_bbcode_data());
		$this->uninstall_bbcode($this->spoilerequals_bbcode_data());
	}

	/**
	 * Uninstall the BBCode from the database.
	 *
	 * @return void
	 */
	public function uninstall_bbcode($data)
	{
		if (empty($data))
		{
			return;
		}

		$this->remove_bbcode($data['bbcode_tag']);
	}

	/**
	 * Check whether BBCode already exists.
	 *
	 * @param string $bbcode_tag
	 *
	 * @return integer
	 */
	public function bbcode_exists($bbcode_tag = '')
	{
		if (empty($bbcode_tag))
		{
			return -1;
		}

		$sql = 'SELECT bbcode_id
				FROM ' . BBCODES_TABLE . '
				WHERE ' . $this->db->sql_build_array('SELECT', ['bbcode_tag' => $bbcode_tag]);
		$result = $this->db->sql_query($sql);
		$bbcode_id = (int) $this->db->sql_fetchfield('bbcode_id');
		$this->db->sql_freeresult($result);

		// Set invalid index if BBCode doesn't exist to avoid
		// getting the first record of the table
		$bbcode_id = $bbcode_id > NUM_CORE_BBCODES ? $bbcode_id : -1;

		return $bbcode_id;
	}

	/**
	 * Calculate the ID for the BBCode that is about to be installed.
	 *
	 * @return integer
	 */
	public function bbcode_id()
	{
		$sql = 'SELECT MAX(bbcode_id) as last_id
			FROM ' . BBCODES_TABLE;
		$result = $this->db->sql_query($sql);
		$bbcode_id = (int) $this->db->sql_fetchfield('last_id');
		$this->db->sql_freeresult($result);
		$bbcode_id += 1;

		if ($bbcode_id <= NUM_CORE_BBCODES)
		{
			$bbcode_id = NUM_CORE_BBCODES + 1;
		}

		return $bbcode_id;
	}


	/**
	 * Add the BBCode in the database.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function add_bbcode($data = [])
	{
		if (empty($data) ||
			(!empty($data['bbcode_id']) && (int) $data['bbcode_id'] > BBCODE_LIMIT))
		{
			return;
		}

		$sql = 'INSERT INTO ' . BBCODES_TABLE . '
			' . $this->db->sql_build_array('INSERT', $data);
			
		
		$this->db->sql_query($sql);

	}

	/**
	 * Remove BBCode by tag.
	 *
	 * @param string $bbcode_tag
	 *
	 * @return void
	 */
	public function remove_bbcode($bbcode_tag = '')
	{
		if (empty($bbcode_tag))
		{
			return;
		}

		$bbcode_id = (int) $this->bbcode_exists($bbcode_tag);

		// Remove only if exists
		if ($bbcode_id > NUM_CORE_BBCODES)
		{
			$sql = 'DELETE FROM ' . BBCODES_TABLE . '
				WHERE bbcode_id = ' . $bbcode_id;
			
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Update BBCode data if it already exists.
	 *
	 * @param integer	$bbcode_id
	 * @param array		$data
	 *
	 * @return void
	 */
	public function update_bbcode($bbcode_id = -1, $data = [])
	{
		$bbcode_id = (int) $bbcode_id;

		if ($bbcode_id <= NUM_CORE_BBCODES || empty($data))
		{
			return;
		}

		unset($data['bbcode_id']);

		$sql = 'UPDATE ' . BBCODES_TABLE . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE bbcode_id = ' . $bbcode_id;
			
		
		$this->db->sql_query($sql);
	}

	/**
	 * BBCode data used in the migration files.
	 *
	 * @return array
	 */
	public function countdown_bbcode_data()
	{
		return [
			'bbcode_tag'	=> 'countdown',
			'bbcode_match'	=> '[countdown]{TEXT}[/countdown]',
			'bbcode_tpl'	=> '<span class="countdown">{TEXT}</span>',
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 1
		];
	}

	public function post_bbcode_data()
	{
		return [
			'bbcode_tag'	=> 'post=',
			'bbcode_match'	=> '[post=#{NUMBER}]{TEXT2}[/post]',
			'bbcode_tpl'	=> '<a class="postlink post_tag" href="{SERVER_PROTOCOL}{SERVER_NAME}{SCRIPT_PATH}viewtopic.php?p={NUMBER}#p{NUMBER}">{TEXT2}</a>',
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 1
		];
	}

	public function dice_bbcode_data()
	{
		return [
			'bbcode_tag'	=> 'dice=',
			'bbcode_match'	=> '[dice]{TEXT}[/dice]',
			'bbcode_tpl'	=> '<span class="dice-tag-original">{TEXT}</span>',
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 1
		];
	}

	private function base_votecount_tpl_string()
	{
		
		$tplString = '<div style="margin: 0 20px 20px 20px;"><div class="quotetitle"><b>Votecount: {TEXT2}</b> <input class="button2" type="button" value="Show" style="width:45px;font-size:12px;margin:0px;" ';
		
		$tplString = $tplString . 'onclick="if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\'; this.innerText = \'\'; this.value = \'Hide\'; } else { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\'; this.innerText = \'\'; this.value = \'Show\'; }" /></div><div class="quotecontent"><div style="display: none;">{TEXT}</div></div></div>';
		
		return $tplString;
	}

	
	public function spoilerequals_bbcode_data()
	{
		$spoilerString = "<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <div style=\"margin:20px; margin-top:1px; margin-bottom:1px;\"><div class=\"quotetitle\"><b>Spoiler: {TEXT1}</b> <input type=\"button\" value=\"Show\" class=\"button2\" onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerText = ''; this.value = 'Hide'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerText = ''; this.value = 'Show'; }\" /></div><div class=\"quotecontent\"><div style=\"display: none;\">{TEXT2}</div></div></div>\n        </xsl:when>\n        <xsl:otherwise>\n                <div style=\"display: inline; color:#000000 !important; background:#000000 !important; padding:0px 3px;\"  title=\"This text is hidden to prevent spoilers; to reveal, highlight it with your cursor.\">{TEXT2}</div>\n        </xsl:otherwise>\n</xsl:choose>";
		return [
			'bbcode_tag'	=> 'spoiler=',
			'bbcode_match'	=> '[spoiler={TEXT1;optional}]{TEXT2}[/spoiler]',
			'bbcode_tpl'	=> $spoilerString,
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 1
		];
	}
	public function votecount_bbcode_data()
	{
		$votecountString = str_replace('{TEXT2}','', $this->base_votecount_tpl_string());
		
		return [
			'bbcode_tag'	=> 'votecount',
			'bbcode_match'	=> '[votecount]{TEXT}[/votecount]',
			'bbcode_tpl'	=> $votecountString,
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 0
		];
	}


	public function votecountbbcode_bbcode_data()
	{
		
		$votecountString = str_replace('{TEXT2}','', $this->base_votecount_tpl_string());
		$votecountString = str_replace('Votecount','VotecountBBCode', $votecountString);
		return [
			'bbcode_tag'	=> 'votecountBBCode',
			'bbcode_match'	=> '[votecountBBCode]{TEXT}[/votecountBBCode]',
			'bbcode_tpl'	=> $votecountString,
			'bbcode_helpline'	=> '',
			'display_on_posting'	=> 0
		];
	}
}
