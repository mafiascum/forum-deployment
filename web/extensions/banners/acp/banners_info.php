<?php

namespace mafiascum\banners\acp;

class banners_info
{
    public function module()
    {
        return array(
            'filename' => '\mafiascum\banners\acp\banners_module',
            'title'    => 'ACP_BANNERS_TITLE',
            'modes'    => array(
                'manage' => array(
                    'title' => 'ACP_BANNERS_TITLE',
                    'auth'  => 'ext_mafiascum/banners && acl_a_banners',
                    'cat'   => array('ACP_CAT_USERS'),
                ),
            ),
        );
    }
}
