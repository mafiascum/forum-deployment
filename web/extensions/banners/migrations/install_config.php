<?php

namespace mafiascum\banners\migrations;

class install_config extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config['banners_enabled']);
    }

    public static function depends_on()
    {
        return array('\mafiascum\banners\migrations\install_ucp_module');
    }

    public function update_data()
    {
        return array(
            array('config.add', array('banners_enabled', 1)),
        );
    }
}
