<?php

namespace mafiascum\votecounter\migrations;

class whitelist_topics_for_vc extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists(
            $this->table_prefix . 'votecounter_topics'
        );
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'votecounter_topics' => [
                    'COLUMNS' => [
                        'topic_id'   => ['UINT', 0],
                        'created_at' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'topic_id',
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'votecounter_topics',
            ],
        ];
    }
}
