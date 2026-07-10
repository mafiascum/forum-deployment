<?php

namespace mafiascum\alt_switcher\migrations;

class alt_switcher_browsers extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'alt_switcher_browsers');
    }

    static public function depends_on()
    {
        return array('\mafiascum\alt_switcher\migrations\alt_switcher_sessions');
    }

    public function update_schema()
    {
        return array(
            'add_tables' => array(
                $this->table_prefix . 'alt_switcher_browsers' => array(
                    'COLUMNS' => array(
                        'browser_id'    => array('VCHAR:64', ''),
                        'bound_ip'      => array('VCHAR:40', ''),
                        'bound_ua_hash' => array('VCHAR:64', ''),
                        'created_at'    => array('TIMESTAMP', 0),
                    ),
                    'PRIMARY_KEY' => 'browser_id',
                ),
            ),
        );
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'alt_switcher_browsers',
            ),
        );
    }
}
?>
