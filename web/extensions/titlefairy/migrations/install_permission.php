<?php

namespace mafiascum\titlefairy\migrations;

class install_permission extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = 'SELECT auth_option_id
                FROM ' . $this->table_prefix . "acl_options
                WHERE auth_option = 'a_titlefairy'";
        $result = $this->db->sql_query($sql);
        $exists = $this->db->sql_fetchfield('auth_option_id');
        $this->db->sql_freeresult($result);

        return $exists !== false;
    }

    public static function depends_on()
    {
        return array('\phpbb\db\migration\data\v330\v330');
    }

    public function update_data()
    {
        return array(
            array('permission.add', array('a_titlefairy', true)),
            array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_titlefairy')),
        );
    }
}
