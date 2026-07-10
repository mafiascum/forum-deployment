<?php

namespace mafiascum\alt_switcher\controller;

use mafiascum\alt_switcher\includes\AltSessionManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AddAccountController
{
    protected $user;
    protected $request;
    protected $language;
    protected $helper;
    protected $alt_manager;
    protected $root_path;
    protected $php_ext;

    public function __construct(\phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\language\language $language, \phpbb\controller\helper $helper, AltSessionManager $alt_manager, $root_path, $php_ext)
    {
        $this->user = $user;
        $this->request = $request;
        $this->language = $language;
        $this->helper = $helper;
        $this->alt_manager = $alt_manager;
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
    }

    public function handle()
    {
        if ((int) $this->user->data['user_id'] === ANONYMOUS) {
            return new RedirectResponse(append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login'));
        }

        $hash = $this->request->variable('hash', '');
        if (!check_link_hash($hash, 'alt_switcher_add')) {
            trigger_error('FORM_INVALID');
        }

        $browser_id = $this->alt_manager->ensure_browser_id();
        $this->alt_manager->background_user($browser_id, (int) $this->user->data['user_id']);

        $this->user->session_kill(false);

        $login_url = append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login&alt_switcher_adding=1');
        return new RedirectResponse($login_url);
    }
}
?>
