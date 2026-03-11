<?php

namespace mafiascum\votecounter\acp;

class vc_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $request, $template, $config, $user;

        $this->tpl_name = 'acp_vc_body';
        $this->page_title = 'ACP_VC_TITLE';
        add_form_key('acp_vc');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('acp_vc')) trigger_error('FORM_INVALID');
            $bot_id = (int) $request->variable('vc_bot_user_id', 0);
            set_config('vc_bot_user_id', $bot_id);
            trigger_error('Settings saved.' . adm_back_link($this->u_action));
        }

        $template->assign_vars([
            'VC_BOT_USER_ID' => $config['vc_bot_user_id'] ?? 0,
            'U_ACTION' => $this->u_action,
        ]);
    }
}
