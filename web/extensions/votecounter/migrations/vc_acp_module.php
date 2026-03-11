<?php

namespace mafiascum\votecounter\migrations;

class vc_acp_module extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config['vc_bot_user_id']);
    }
    static public function depends_on()
    {
        return [];
    }
    public function update_data()
    {
        return [
            ['config.add', ['vc_bot_user_id', 0]],
            ['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_VC_TITLE']],
            [
                'module.add',
                [
                    'acp',
                    'ACP_VC_TITLE',
                    [
                        'module_basename' => '\mafiascum\votecounter\acp\vc_module',
                        'modes' => [
                            'manage' => [
                                'auth' => 'acl_a_board'
                            ]
                        ]
                    ],
                ]
            ]
        ];
    }
}
