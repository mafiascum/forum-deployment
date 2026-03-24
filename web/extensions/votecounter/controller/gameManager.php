<?php

namespace mafiascum\votecounter\controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class gameManager
{
    protected $config;
    protected $helper;
    protected $language;
    protected $template;
    protected $db;
    protected $user;
    protected $auth;
    protected $request;

    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template,    \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request $request)
    {
        $this->config   = $config;
        $this->helper   = $helper;
        $this->language = $language;
        $this->template = $template;
        $this->db       = $db;
        $this->user     = $user;
        $this->auth     = $auth;
        $this->request  = $request;
    }

    public function handle($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $permitted = $this->auth->acl_get('m_edit', $topic_id);
        if (!$permitted) {
            trigger_error('NO_PERMISSION');
            return;
        }

        $active_tab = $this->request->variable('tab', 'players');

        $this->template->assign_vars([
            'TOPIC_ID' => $topic_id,
            'ACTIVE_TAB' => $active_tab,
            'MANAGE_GAME_CSS' => "{T_ASSETS_PATH}/styles/manage_game.css",
            'U_TAB_PLAYERS' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'players']),
            'U_TAB_DAYS'    => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'days']),
            'U_TAB_SETTINGS' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'settings']),

        ]);

        return $this->helper->render('manage_game.html', $this->language->lang('MANAGE_GAME'));
    }

    public function tab($topic_id, $tab)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $permitted = $this->auth->acl_get('m_edit', $topic_id);
        if (!$permitted) {
            trigger_error('NO_PERMISSION');
            return;
        }

        $tab_templates = [
            'players'  => 'forms/players.html',
            'days'     => 'forms/days.html',
            'settings' => 'forms/misc.html',
        ];

        if (!isset($tab_templates[$tab])) {
            trigger_error('INVALID_TAB');
        }

        $template_file = $tab_templates[$tab];

        switch ($tab) {
            case 'players':
                break;
            case 'days':
                break;
            case 'settings':
                break;
        }

        $this->template->set_filenames(['body' => $template_file]);
        $this->template->display('body');
        exit;
    }
}
