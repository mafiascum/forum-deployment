<?php

namespace mafiascum\miscellaneous\migrations;

class ucp_hidden_topics extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		$sql = 'SELECT module_id
			FROM ' . $this->table_prefix . "modules
			WHERE module_class = 'ucp'
				AND module_basename = '\\\\mafiascum\\\\miscellaneous\\\\ucp\\\\main_module'
				AND module_mode = 'hidden_topics'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return !empty($row);
	}

	static public function depends_on()
	{
		return array('\mafiascum\miscellaneous\migrations\hidden_topics_v1');
	}

	public function update_data()
	{
		return array(
			array('module.add', array(
				'ucp',
				'UCP_PREFS',
				array(
					'module_basename' => '\mafiascum\miscellaneous\ucp\main_module',
					'modes' => array('hidden_topics'),
				),
			)),
		);
	}
}
