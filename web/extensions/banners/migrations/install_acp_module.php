<?php

namespace mafiascum\banners\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = 'SELECT module_id
                FROM ' . $this->table_prefix . "modules
                WHERE module_class = 'acp'
                AND module_langname = 'ACP_BANNERS_TITLE'";
        $result    = $this->db->sql_query($sql);
        $module_id = $this->db->sql_fetchfield('module_id');
        $this->db->sql_freeresult($result);

        return $module_id !== false;
    }

    public static function depends_on()
    {
        return array('\mafiascum\banners\migrations\install_permission');
    }

    public function update_data()
    {
        return array(
            array('module.add', array(
                'acp',
                'ACP_CAT_USERS',
                array(
                    'module_basename' => '\mafiascum\banners\acp\banners_module',
                    'modes'           => array('manage'),
                ),
            )),
        );
    }
}
