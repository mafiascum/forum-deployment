<?php

namespace mafiascum\votecounter\migrations;

class add_paused_at_to_games extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_column_exists($this->table_prefix . 'games', 'paused_at');
    }

    static public function depends_on()
    {
        return array('\mafiascum\votecounter\migrations\votecount_db_tables');
    }

    public function update_schema()
    {
        return [
            'add_columns' => [
                $this->table_prefix . 'games' => [
                    'paused_at' => ['UINT', null],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_columns' => [
                $this->table_prefix . 'games' => [
                    'paused_at',
                ],
            ],
        ];
    }
}
