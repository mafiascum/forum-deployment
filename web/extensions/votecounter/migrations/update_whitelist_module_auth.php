<?php

namespace mafiascum\votecounter\migrations;

class update_whitelist_module_auth extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = 'SELECT module_auth
                FROM ' . $this->table_prefix . "modules
                WHERE module_class = 'acp'
                AND module_basename = '\\\\mafiascum\\\\votecounter\\\\acp\\\\whitelist_module'
                AND module_mode = 'manage'";
        $result = $this->db->sql_query($sql);
        $auth = $this->db->sql_fetchfield('module_auth');
        $this->db->sql_freeresult($result);

        return $auth === 'ext_mafiascum/votecounter && acl_a_votecounter_whitelist';
    }

    public static function depends_on()
    {
        return array(
            '\mafiascum\votecounter\migrations\vc_acp_module',
            '\mafiascum\votecounter\migrations\install_whitelist_permission',
        );
    }

    public function update_data()
    {
        return array(
            array('custom', array(array($this, 'update_module_auth'))),
        );
    }

    public function update_module_auth()
    {
        $sql = 'UPDATE ' . $this->table_prefix . "modules
                SET module_auth = 'ext_mafiascum/votecounter && acl_a_votecounter_whitelist'
                WHERE module_class = 'acp'
                AND module_basename = '\\\\mafiascum\\\\votecounter\\\\acp\\\\whitelist_module'
                AND module_mode = 'manage'";
        $this->db->sql_query($sql);
    }
}
