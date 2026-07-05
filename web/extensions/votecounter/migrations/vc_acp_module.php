<?php

namespace mafiascum\votecounter\migrations;

class vc_acp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = 'SELECT module_id
                FROM ' . $this->table_prefix . "modules
                WHERE module_class = 'acp'
                AND module_langname = 'ACP_VC_TITLE'";
        $result = $this->db->sql_query($sql);
        $module_id = $this->db->sql_fetchfield('module_id');
        $this->db->sql_freeresult($result);

        return $module_id !== false;
    }

    static public function depends_on()
    {
        return array('\mafiascum\votecounter\migrations\whitelist_topics_for_vc');
    }

    public function update_data()
    {
        return array(
            array('module.add', array(
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_VC_TITLE',
            )),
            array('module.add', array(
                'acp',
                'ACP_VC_TITLE',
                array(
                    'module_basename' => '\mafiascum\votecounter\acp\whitelist_module',
                    'modes'           => array('manage'),
                ),
            )),
        );
    }
}
