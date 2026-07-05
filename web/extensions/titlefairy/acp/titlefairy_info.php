<?php

namespace mafiascum\titlefairy\acp;

class titlefairy_info
{
    public function module()
    {
        return array(
            'filename' => '\mafiascum\titlefairy\acp\titlefairy_module',
            'title'    => 'ACP_TITLEFAIRY_TITLE',
            'modes'    => array(
                'manage' => array(
                    'title' => 'ACP_TITLEFAIRY_TITLE',
                    'auth'  => 'ext_mafiascum/titlefairy && acl_a_titlefairy',
                    'cat'   => array('ACP_CAT_USERS'),
                ),
            ),
        );
    }
}
