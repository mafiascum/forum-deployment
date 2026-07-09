<?php
/**
 *
 * @package phpBB Extension - Mafiascum Hide Email On Registration
 * @copyright (c) 2018 mafiascum.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace mafiascum\mcp\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
			'core.user_setup' => 'load_language_on_setup',
			'core.report_post_auth' => 'report_post_auth',
			'core.mcp_reports_report_details_query_after' => 'mcp_reports_report_details_query_after',
			'core.mcp_report_template_data' => 'mcp_report_template_data',
			'core.mcp_reports_modify_post_row' => 'mcp_reports_modify_post_row',
			'core.submit_post_modify_sql_data' => 'snapshot_pre_edit',
			'core.mcp_post_template_data' => 'mcp_post_add_edit_history',
        );
    }

    /**
     * Constructor
     *
     */
    public function __construct(\phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\auth\auth $auth, $table_prefix, $root_path, $php_ext)
    {
		$this->template = $template;
		$this->db = $db;
		$this->user = $user;
		$this->auth = $auth;
		$this->table_prefix = $table_prefix;
		$this->phpbb_root_path = $root_path;
		$this->php_ext = $php_ext;
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'mafiascum/mcp',
            'lang_set' => 'common',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

	function mcp_reports_modify_post_row($event) {
		$post_row = $event['post_row'];
		$row = $event['row'];

		$post_row['POST_SUBJECT'] = ($row['topic_title']) ? $row['topic_title'] : $this->user->lang['NO_SUBJECT'];

		$event['post_row'] = $post_row;
	}

	function mcp_report_template_data($event) {
		$report_template = $event['report_template'];
		$post_info = $event['post_info'];

		$report_template['POST_SUBJECT'] = ($post_info['topic_title']) ? $post_info['topic_title'] : $this->user->lang['NO_SUBJECT'];

		$event['report_template'] = $report_template;
	}

	public function report_post_auth($event) {
		//This prevents blocking multiple reports on the same post from being submitted.
		$report_data = $event['report_data'];

		$report_data['post_reported'] = false;

		$event['report_data'] = $report_data;
   }

   function mcp_reports_report_details_query_after($event) {
		//Query all reports for the post & throw into template.
		$sql_ary = $event['sql_ary'];
		$sql_ary['WHERE'] = $sql_ary['WHERE'] . ' AND r.report_closed = 0';
		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while($report = $this->db->sql_fetchrow($result)) {
			$report_id = $report['report_id'];
			$reason = array('title' => $report['reason_title'], 'description' => $report['reason_description']);

			if (isset($this->user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])]) && isset($this->user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])]))
			{
				$reason['description'] = $this->user->lang['report_reasons']['DESCRIPTION'][strtoupper($reason['title'])];
				$reason['title'] = $this->user->lang['report_reasons']['TITLE'][strtoupper($reason['title'])];
			}
			$this->template->assign_block_vars('reportrow', array(
				'REPORT_DATE'				=> $this->user->format_date($report['report_time']),
				'REPORT_REASON_TITLE'		=> $reason['title'],
				'REPORT_REASON_DESCRIPTION'	=> $reason['description'],
				'REPORT_TEXT'				=> $report['report_text'],
				'REPORTER_FULL'				=> get_username_string('full', $report['user_id'], $report['username'], $report['user_colour']),
				'REPORT_ID'					=> $report['report_id'],
			));
		}

		$this->db->sql_freeresult($result);
   }

	/**
	 * Snapshot the pre-edit state of a post into the edit history table.
	 * Fires just before phpBB runs the UPDATE on the posts row, so the DB
	 * still holds the previous subject/text/bbcode fields.
	 */
	public function snapshot_pre_edit($event) {
		$post_mode = $event['post_mode'];
		if (!in_array($post_mode, ['edit', 'edit_topic', 'edit_first_post', 'edit_last_post'], true)) {
			return;
		}

		$data = $event['data'];
		$post_id = isset($data['post_id']) ? (int) $data['post_id'] : 0;
		if (!$post_id) {
			return;
		}

		$sql = 'SELECT post_subject, post_text, bbcode_uid, bbcode_bitfield,
					   enable_bbcode, enable_smilies, enable_magic_url
				FROM ' . POSTS_TABLE . '
				WHERE post_id = ' . $post_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row) {
			return;
		}

		// Skip if neither text nor subject actually changed.
		$new_text = isset($data['message']) ? $data['message'] : $row['post_text'];
		$new_subject = isset($data['post_subject']) ? $data['post_subject'] : $row['post_subject'];
		if ($new_text === $row['post_text'] && $new_subject === $row['post_subject']) {
			return;
		}

		$insert = [
			'post_id'                 => $post_id,
			'editor_user_id'          => (int) $this->user->data['user_id'],
			'edit_time'               => time(),
			'edit_reason'             => isset($data['post_edit_reason']) ? (string) $data['post_edit_reason'] : '',
			'post_subject_before'     => (string) $row['post_subject'],
			'post_text_before'        => (string) $row['post_text'],
			'bbcode_uid_before'       => (string) $row['bbcode_uid'],
			'bbcode_bitfield_before'  => (string) $row['bbcode_bitfield'],
			'enable_bbcode_before'    => (int) $row['enable_bbcode'],
			'enable_smilies_before'   => (int) $row['enable_smilies'],
			'enable_magic_url_before' => (int) $row['enable_magic_url'],
		];

		$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'post_edit_history ' . $this->db->sql_build_array('INSERT', $insert));
	}

	/**
	 * Render prior versions of the post on mcp_post_details for users with
	 * moderator edit rights on the forum, or global admins.
	 */
	public function mcp_post_add_edit_history($event) {
		$post_info = $event['post_info'];
		if (empty($post_info['post_id'])) {
			return;
		}

		$post_id = (int) $post_info['post_id'];
		$forum_id = (int) $post_info['forum_id'];

		$is_admin = (bool) $this->auth->acl_get('a_');
		if (!$is_admin) {
			if (!$this->auth->acl_get('m_edit', $forum_id)) {
				return;
			}
			if (!$this->registered_can_read_forum($forum_id)) {
				return;
			}
		}

		if (!function_exists('generate_text_for_display')) {
			include_once $this->phpbb_root_path . 'includes/functions_content.' . $this->php_ext;
		}

		$sql = 'SELECT h.*, u.username, u.user_colour
				FROM ' . $this->table_prefix . 'post_edit_history h
				LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = h.editor_user_id
				WHERE h.post_id = ' . $post_id . '
				ORDER BY h.edit_time DESC';
		$result = $this->db->sql_query($sql);

		$count = 0;
		while ($row = $this->db->sql_fetchrow($result)) {
			$flags = ($row['enable_bbcode_before'] ? OPTION_FLAG_BBCODE : 0)
				| ($row['enable_smilies_before'] ? OPTION_FLAG_SMILIES : 0)
				| ($row['enable_magic_url_before'] ? OPTION_FLAG_LINKS : 0);

			$preview = generate_text_for_display(
				$row['post_text_before'],
				$row['bbcode_uid_before'],
				$row['bbcode_bitfield_before'],
				$flags
			);

			$this->template->assign_block_vars('edithistory', [
				'EDITOR'           => get_username_string('full', (int) $row['editor_user_id'], $row['username'] ?: '', $row['user_colour'] ?: ''),
				'EDIT_TIME'        => $this->user->format_date($row['edit_time']),
				'EDIT_REASON'      => $row['edit_reason'],
				'PREVIOUS_SUBJECT' => $row['post_subject_before'],
				'PREVIOUS_TEXT'    => $preview,
			]);
			$count++;
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'S_HAS_EDIT_HISTORY' => $count > 0,
			'EDIT_HISTORY_COUNT' => $count,
		]);
	}

	private function registered_can_read_forum($forum_id) {
		$forum_id = (int) $forum_id;

		$sql = 'SELECT 1
				FROM ' . ACL_OPTIONS_TABLE . ' ao
				JOIN ' . ACL_GROUPS_TABLE . ' ag ON ag.auth_option_id = ao.auth_option_id
				JOIN ' . GROUPS_TABLE . ' g ON g.group_id = ag.group_id
				WHERE ao.auth_option = \'f_read\'
					AND g.group_name = \'REGISTERED\'
					AND ag.forum_id = ' . $forum_id . '
					AND ag.auth_setting = 1
				UNION
				SELECT 1
				FROM ' . ACL_OPTIONS_TABLE . ' ao
				JOIN ' . ACL_ROLES_DATA_TABLE . ' rd ON rd.auth_option_id = ao.auth_option_id
				JOIN ' . ACL_GROUPS_TABLE . ' ag ON ag.auth_role_id = rd.role_id
				JOIN ' . GROUPS_TABLE . ' g ON g.group_id = ag.group_id
				WHERE ao.auth_option = \'f_read\'
					AND g.group_name = \'REGISTERED\'
					AND ag.forum_id = ' . $forum_id . '
					AND rd.auth_setting = 1';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return (bool) $row;
	}
}
