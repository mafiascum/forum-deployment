<?php

namespace mafiascum\votecounter\acp;

class whitelist_info
{
    public function module()
    {
        return array(
            'filename' => '\mafiascum\votecounter\acp\whitelist_module',
            'title'    => 'ACP_VC_TITLE',
            'modes'    => array(
                'manage' => array(
                    'title' => 'ACP_VC_WHITELIST',
                    'auth'  => 'ext_mafiascum/votecounter && acl_a_board',
                    'cat'   => array('ACP_VC_TITLE'),
                ),
            ),
        );
    }
}
