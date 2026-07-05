<?php

namespace mafiascum\votecounter\migrations;

class add_added_by_to_whitelist extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'votecounter_topics', 'added_by_user_id');
    }

    static public function depends_on()
    {
        return array('\mafiascum\votecounter\migrations\whitelist_topics_for_vc');
    }

    public function update_schema()
    {
        return [
            'add_columns' => [
                $this->table_prefix . 'votecounter_topics' => [
                    'added_by_user_id' => ['UINT', null],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_columns' => [
                $this->table_prefix . 'votecounter_topics' => [
                    'added_by_user_id',
                ],
            ],
        ];
    }
}
