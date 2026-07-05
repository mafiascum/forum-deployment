<?php

namespace mafiascum\banners\migrations;

class install_banner_tables extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'banners');
    }

    public static function depends_on()
    {
        return array('\phpbb\db\migration\data\v330\v330');
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'banners' => [
                    'COLUMNS' => [
                        'banner_id' => ['UINT', null, 'auto_increment'],
                        'banner_name' => ['VCHAR:255', ''],
                        'banner_image_url' => ['VCHAR:255', ''],
                        'is_public' => ['BOOL', 0],
                    ],
                    'PRIMARY_KEY' => 'banner_id',
                ],

                $this->table_prefix . 'banner_grants' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'banner_id' => ['UINT', 0],
                        'user_id' => ['UINT', 0],
                        'granted_by' => ['UINT', 0],
                        'created_at' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'banner_user_unq' => ['UNIQUE', ['banner_id', 'user_id']],
                        'user_idx' => ['INDEX', 'user_id'],
                    ],
                ],

                $this->table_prefix . 'user_banners' => [
                    'COLUMNS' => [
                        'id' => ['UINT', null, 'auto_increment'],
                        'user_id' => ['UINT', 0],
                        'banner_id' => ['UINT', 0],
                        'slot' => ['UINT', 1],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'user_slot_unq' => ['UNIQUE', ['user_id', 'slot']],
                        'user_idx' => ['INDEX', 'user_id'],
                    ],
                ],
            ]
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'user_banners',
                $this->table_prefix . 'banner_grants',
                $this->table_prefix . 'banners',
            ],
        ];
    }
}
