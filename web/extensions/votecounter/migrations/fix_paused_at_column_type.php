<?php

namespace mafiascum\votecounter\migrations;

class fix_paused_at_column_type extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return false;
    }

    static public function depends_on()
    {
        return array('\mafiascum\votecounter\migrations\add_paused_at_to_games');
    }

    public function update_schema()
    {
        return [
            'change_columns' => [
                $this->table_prefix . 'games' => [
                    'paused_at' => ['TIMESTAMP', null],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        return [
            'change_columns' => [
                $this->table_prefix . 'games' => [
                    'paused_at' => ['UINT', null],
                ],
            ],
        ];
    }
}
