<?php

namespace mafiascum\miscellaneous\ucp;

class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $phpbb_container, $template, $user, $request, $phpbb_root_path, $phpEx;

		$manager = $phpbb_container->get('mafiascum.miscellaneous.hidden_topics_manager');
		$db = $phpbb_container->get('dbal.conn');
		$language = $phpbb_container->get('language');
		$language->add_lang('common', 'mafiascum/miscellaneous');

		$scope = \mafiascum\miscellaneous\hidden\manager::SCOPE_EGOSEARCH;
		$user_id = (int) $user->data['user_id'];

		add_form_key('maf_ucp_hidden_topics');

		if ($request->is_set_post('unhide'))
		{
			if (!check_form_key('maf_ucp_hidden_topics'))
			{
				trigger_error('FORM_INVALID');
			}
			$unhide_ids = $request->variable('unhide_ids', array(0));
			foreach ($unhide_ids as $tid)
			{
				$manager->unhide($user_id, (int) $tid, $scope);
			}
			meta_refresh(3, $this->u_action);
			trigger_error($language->lang('MAF_UCP_HIDDEN_UPDATED') . '<br /><br /><a href="' . $this->u_action . '">' . $language->lang('MAF_UCP_HIDDEN_BACK') . '</a>');
		}

		$hidden_ids = $manager->get_hidden_topic_ids($user_id, $scope);

		if (!empty($hidden_ids))
		{
			$sql = 'SELECT topic_id, topic_title, forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', array_map('intval', $hidden_ids)) . '
				ORDER BY topic_title ASC';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$template->assign_block_vars('hidden_topics', array(
					'TOPIC_ID' => (int) $row['topic_id'],
					'TOPIC_TITLE' => $row['topic_title'],
					'U_VIEWTOPIC' => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . (int) $row['topic_id']),
				));
			}
			$db->sql_freeresult($result);
		}

		$template->assign_vars(array(
			'S_MAF_HAS_HIDDEN' => !empty($hidden_ids),
			'U_ACTION' => $this->u_action,
		));

		$this->tpl_name = 'ucp_hidden_topics';
		$this->page_title = 'MAF_UCP_HIDDEN_TOPICS';
	}
}
