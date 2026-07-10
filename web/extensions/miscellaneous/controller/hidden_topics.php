<?php

namespace mafiascum\miscellaneous\controller;

use Symfony\Component\HttpFoundation\Response;

class hidden_topics
{
	protected $manager;

	protected $request;

	protected $user;

	protected $helper;

	protected $db;

	protected $language;

	public function __construct(\mafiascum\miscellaneous\hidden\manager $manager, \phpbb\request\request $request, \phpbb\user $user, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language)
	{
		$this->manager = $manager;
		$this->request = $request;
		$this->user = $user;
		$this->helper = $helper;
		$this->db = $db;
		$this->language = $language;
	}

	public function toggle($topic_id, $scope)
	{
		$topic_id = (int) $topic_id;

		if (empty($this->user->data['user_id']) || $this->user->data['user_id'] == ANONYMOUS)
		{
			return $this->helper->message('NO_AUTH_OPERATION', array(), 'NO_AUTH_OPERATION', 403);
		}

		if (!in_array($scope, \mafiascum\miscellaneous\hidden\manager::valid_scopes(), true))
		{
			return $this->helper->message('MAF_HIDE_INVALID_SCOPE', array(), 'MAF_HIDE_INVALID_SCOPE', 400);
		}

		if ($topic_id <= 0 || !$this->topic_exists($topic_id))
		{
			return $this->helper->message('NO_TOPIC', array(), 'NO_TOPIC', 404);
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'maf_hide_topic'))
		{
			return $this->helper->message('FORM_INVALID', array(), 'FORM_INVALID', 400);
		}

		$user_id = (int) $this->user->data['user_id'];
		if ($this->manager->is_hidden($user_id, $topic_id, $scope))
		{
			$this->manager->unhide($user_id, $topic_id, $scope);
		}
		else
		{
			$this->manager->hide($user_id, $topic_id, $scope);
		}

		global $phpbb_root_path, $phpEx;
		$url = append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $topic_id);
		return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
	}

	protected function topic_exists($topic_id)
	{
		$sql = 'SELECT 1 FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return !empty($row);
	}
}
