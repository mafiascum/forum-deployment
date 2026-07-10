<?php

namespace mafiascum\miscellaneous\migrations;

class hidden_topics_v1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'maf_hidden_topics');
	}

	static public function depends_on()
	{
		return array('\mafiascum\miscellaneous\migrations\miscellaneous');
	}

	public function update_schema()
	{
		return array(
			'add_tables' => array(
				$this->table_prefix . 'maf_hidden_topics' => array(
					'COLUMNS' => array(
						'user_id'   => array('UINT', 0),
						'topic_id'  => array('UINT', 0),
						'scope'     => array('VCHAR:24', ''),
						'hidden_at' => array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY' => array('user_id', 'topic_id', 'scope'),
					'KEYS' => array(
						'user_scope' => array('INDEX', array('user_id', 'scope')),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array($this->table_prefix . 'maf_hidden_topics'),
		);
	}
}
