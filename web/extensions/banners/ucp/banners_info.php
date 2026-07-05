<?php

namespace mafiascum\banners\ucp;

class banners_info
{
    public function module()
    {
        return array(
            'filename' => '\mafiascum\banners\ucp\banners_module',
            'title'    => 'UCP_BANNERS',
            'modes'    => array(
                'manage' => array(
                    'title' => 'UCP_BANNERS',
                    'auth'  => 'ext_mafiascum/banners',
                    'cat'   => array('UCP_PROFILE'),
                ),
            ),
        );
    }
}
