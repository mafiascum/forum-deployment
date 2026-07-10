<?php

namespace mafiascum\miscellaneous\hidden;

class manager
{
	const SCOPE_EGOSEARCH = 'egosearch';

	static public function valid_scopes()
	{
		return array(self::SCOPE_EGOSEARCH);
	}

	protected $db;

	protected $table;

	public function __construct(\phpbb\db\driver\driver_interface $db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}

	public function hide($user_id, $topic_id, $scope)
	{
		if (!in_array($scope, self::valid_scopes(), true) || !$user_id || !$topic_id)
		{
			return false;
		}

		$data = array(
			'user_id'   => (int) $user_id,
			'topic_id'  => (int) $topic_id,
			'scope'     => $scope,
			'hidden_at' => time(),
		);

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE user_id = ' . (int) $user_id . '
				AND topic_id = ' . (int) $topic_id . "
				AND scope = '" . $this->db->sql_escape($scope) . "'";
		$this->db->sql_query($sql);

		$this->db->sql_query('INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', $data));

		return true;
	}

	public function unhide($user_id, $topic_id, $scope)
	{
		if (!in_array($scope, self::valid_scopes(), true) || !$user_id || !$topic_id)
		{
			return false;
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE user_id = ' . (int) $user_id . '
				AND topic_id = ' . (int) $topic_id . "
				AND scope = '" . $this->db->sql_escape($scope) . "'";
		$this->db->sql_query($sql);

		return true;
	}

	public function is_hidden($user_id, $topic_id, $scope)
	{
		if (!in_array($scope, self::valid_scopes(), true) || !$user_id || !$topic_id)
		{
			return false;
		}

		$sql = 'SELECT 1 FROM ' . $this->table . '
			WHERE user_id = ' . (int) $user_id . '
				AND topic_id = ' . (int) $topic_id . "
				AND scope = '" . $this->db->sql_escape($scope) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return !empty($row);
	}

	public function get_hidden_topic_ids($user_id, $scope)
	{
		if (!in_array($scope, self::valid_scopes(), true) || !$user_id)
		{
			return array();
		}

		$sql = 'SELECT topic_id FROM ' . $this->table . '
			WHERE user_id = ' . (int) $user_id . "
				AND scope = '" . $this->db->sql_escape($scope) . "'";
		$result = $this->db->sql_query($sql);

		$topic_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$topic_ids[] = (int) $row['topic_id'];
		}
		$this->db->sql_freeresult($result);

		return $topic_ids;
	}
}
