<?php

namespace mafiascum\banners\migrations;

class install_ucp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $sql = 'SELECT module_id
                FROM ' . $this->table_prefix . "modules
                WHERE module_class = 'ucp'
                AND module_langname = 'UCP_BANNERS'";
        $result    = $this->db->sql_query($sql);
        $module_id = $this->db->sql_fetchfield('module_id');
        $this->db->sql_freeresult($result);

        return $module_id !== false;
    }

    public static function depends_on()
    {
        return array('\mafiascum\banners\migrations\install_acp_module');
    }

    public function update_data()
    {
        return array(
            array('module.add', array(
                'ucp',
                'UCP_PROFILE',
                array(
                    'module_basename' => '\mafiascum\banners\ucp\banners_module',
                    'modes'           => array('manage'),
                ),
            )),
        );
    }
}
