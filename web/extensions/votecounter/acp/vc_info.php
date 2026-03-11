<?php

namespace mafiascum\votecounter\acp;

class vc_info
{
    public function module()
    {
        return array(
            'filename'  => '\mafiascum\votecounter\acp\vc_module',
            'title'     => 'ACP_VC_TITLE',
            'modes'    => array(
                'manage'  => array(
                    'title' => 'ACP_VC',
                    'auth'  => 'acl_a_board',
                    'cat'   => array('ACP_VC_TITLE'),
                ),
            ),
        );
    }
}
