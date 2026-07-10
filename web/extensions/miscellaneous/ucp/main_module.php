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

		$user_id = (int) $user->data['user_id'];
		$scopes = \mafiascum\miscellaneous\hidden\manager::valid_scopes();

		add_form_key('maf_ucp_hidden_topics');

		if ($request->is_set_post('unhide'))
		{
			if (!check_form_key('maf_ucp_hidden_topics'))
			{
				trigger_error('FORM_INVALID');
			}
			$unhide = $request->variable('unhide_keys', array(''));
			foreach ($unhide as $key)
			{
				$parts = explode(':', (string) $key, 2);
				if (count($parts) !== 2)
				{
					continue;
				}
				list($scope, $tid) = $parts;
				$manager->unhide($user_id, (int) $tid, $scope);
			}
			meta_refresh(3, $this->u_action);
			trigger_error($language->lang('MAF_UCP_HIDDEN_UPDATED') . '<br /><br /><a href="' . $this->u_action . '">' . $language->lang('MAF_UCP_HIDDEN_BACK') . '</a>');
		}

		$scope_labels = array(
			\mafiascum\miscellaneous\hidden\manager::SCOPE_EGOSEARCH => $language->lang('MAF_UCP_SCOPE_EGOSEARCH'),
			\mafiascum\miscellaneous\hidden\manager::SCOPE_EVERYWHERE => $language->lang('MAF_UCP_SCOPE_EVERYWHERE'),
		);

		$rows_by_scope = array();
		$all_ids = array();
		foreach ($scopes as $scope)
		{
			$ids = $manager->get_hidden_topic_ids($user_id, $scope);
			$rows_by_scope[$scope] = $ids;
			$all_ids = array_merge($all_ids, $ids);
		}
		$all_ids = array_values(array_unique(array_map('intval', $all_ids)));

		$titles = array();
		if (!empty($all_ids))
		{
			$sql = 'SELECT topic_id, topic_title
				FROM ' . TOPICS_TABLE . '
				WHERE ' . $db->sql_in_set('topic_id', $all_ids);
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$titles[(int) $row['topic_id']] = $row['topic_title'];
			}
			$db->sql_freeresult($result);
		}

		$has_any = false;
		foreach ($scopes as $scope)
		{
			foreach ($rows_by_scope[$scope] as $tid)
			{
				if (!isset($titles[(int) $tid]))
				{
					continue;
				}
				$has_any = true;
				$template->assign_block_vars('hidden_topics', array(
					'TOPIC_ID' => (int) $tid,
					'TOPIC_TITLE' => $titles[(int) $tid],
					'SCOPE' => $scope,
					'SCOPE_LABEL' => isset($scope_labels[$scope]) ? $scope_labels[$scope] : $scope,
					'UNHIDE_KEY' => $scope . ':' . (int) $tid,
					'U_VIEWTOPIC' => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . (int) $tid),
				));
			}
		}

		$template->assign_vars(array(
			'S_MAF_HAS_HIDDEN' => $has_any,
			'U_ACTION' => $this->u_action,
		));

		$this->tpl_name = 'ucp_hidden_topics';
		$this->page_title = 'MAF_UCP_HIDDEN_TOPICS';
	}
}
