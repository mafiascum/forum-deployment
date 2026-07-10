<?php

namespace mafiascum\alt_switcher\migrations;

class alt_switcher_sessions extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'alt_switcher_sessions');
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v31x\v314');
    }

    public function update_schema()
    {
        return array(
            'add_tables' => array(
                $this->table_prefix . 'alt_switcher_sessions' => array(
                    'COLUMNS' => array(
                        'id'             => array('UINT', NULL, 'auto_increment'),
                        'browser_id'     => array('VCHAR:64', ''),
                        'user_id'        => array('UINT', 0),
                        'autologin_key'  => array('VCHAR:255', ''),
                        'created_at'     => array('TIMESTAMP', 0),
                        'last_used_at'   => array('TIMESTAMP', 0),
                    ),
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => array(
                        'uniq_browser_user' => array('UNIQUE', array('browser_id', 'user_id')),
                        'browser_id_idx'    => array('INDEX', 'browser_id'),
                        'user_id_idx'       => array('INDEX', 'user_id'),
                    ),
                ),
            ),
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'alt_switcher_sessions',
            ),
        );
    }
}
?>
