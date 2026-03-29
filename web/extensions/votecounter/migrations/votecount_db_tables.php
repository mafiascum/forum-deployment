<?php

namespace mafiascum\votecounter\migrations;

class votecount_db_tables extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'games');
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'games' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'topic_id' => ['UINT', 0],
                        'created_at' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'topic_idx' => ['UNIQUE', 'topic_id']
                    ],
                ],

                $this->table_prefix . 'players' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'game_id' => ['UINT', 0],
                        'user_id' => ['UINT', 0],
                        'died_at' => ['UINT', null],
                        'created_at' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'game_idx' => ['INDEX', 'game_id'],
                        'user_idx' => ['INDEX', 'user_id'],
                        'game_user_unq' => ['UNIQUE', ['game_id', 'user_id']],

                    ],
                ],

                $this->table_prefix . 'game_days' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'game_id' => ['UINT', 0],
                        'day_number' => ['UINT', 0],
                        'start_post_number' => ['UINT', 0],
                        'end_post_number' => ['UINT', null],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'game_idx' => ['INDEX', 'game_id'],
                        'game_days_idx' => ['INDEX', ['game_id', 'day_number']],
                    ],
                ],

                $this->table_prefix . 'game_votes' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'game_id' => ['UINT', 0],
                        'voter_player_id' => ['UINT', 0],
                        'target_player_id' => ['UINT', null],
                        'post_number' => ['UINT', 0]
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'game_post_idx' => ['INDEX', ['game_id', 'post_number']],
                        'voter_idx' => ['INDEX', ['voter_player_id', 'post_number']],
                        'target_idx' => ['INDEX', 'target_player_id'],
                    ],
                ],
            ]
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'game_votes',
                $this->table_prefix . 'game_days',
                $this->table_prefix . 'players',
                $this->table_prefix . 'games',
            ],
        ];
    }
}
