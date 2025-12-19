<?php

namespace mafiascum\votecounter\migrations;

class votecount_slots extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'votecount_slots');
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_schema()
    {
        return array(
            'add_tables' => array(
                $this->table_prefix . 'votecount_slots' => array(
                    'COLUMNS' => array(
                        'slot_id' => array('UINT', null, 'auto_increment'),
                        'topic_id' => array('UINT:10', 0),
                        'user_id' => array('UINT:10', 0),
                    ),
                    'PRIMARY_KEY' => 'slot_id',
                    'KEYS' => [
                        'topic_user' => ['UNIQUE', ['topic_id', 'user_id']],
                    ],
                )
            )
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'votecount_slots'
            )
        );
    }
}
