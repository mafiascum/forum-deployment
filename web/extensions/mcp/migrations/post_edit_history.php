<?php

namespace mafiascum\mcp\migrations;

class post_edit_history extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'post_edit_history');
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_schema()
    {
        return [
            'add_tables' => [
                $this->table_prefix . 'post_edit_history' => [
                    'COLUMNS' => [
                        'edit_id'              => ['UINT', null, 'auto_increment'],
                        'post_id'              => ['UINT', 0],
                        'editor_user_id'       => ['UINT', 0],
                        'edit_time'            => ['TIMESTAMP', 0],
                        'edit_reason'          => ['STEXT_UNI', ''],
                        'post_subject_before'  => ['STEXT_UNI', ''],
                        'post_text_before'    => ['MTEXT_UNI', ''],
                        'bbcode_uid_before'    => ['VCHAR:8', ''],
                        'bbcode_bitfield_before' => ['VCHAR:255', ''],
                        'enable_bbcode_before' => ['BOOL', 1],
                        'enable_smilies_before'=> ['BOOL', 1],
                        'enable_magic_url_before' => ['BOOL', 1],
                    ],
                    'PRIMARY_KEY' => 'edit_id',
                    'KEYS' => [
                        'post_idx' => ['INDEX', 'post_id'],
                    ],
                ],
            ]
        ];
    }

    public function revert_schema()
    {
        return [
            'drop_tables' => [
                $this->table_prefix . 'post_edit_history',
            ],
        ];
    }
}
