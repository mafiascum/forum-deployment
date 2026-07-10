<?php

namespace mafiascum\alt_switcher\controller;

use mafiascum\alt_switcher\includes\AltSessionManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SwitchController
{
    protected $user;
    protected $request;
    protected $language;
    protected $alt_manager;
    protected $root_path;
    protected $php_ext;

    public function __construct(\phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\language\language $language, AltSessionManager $alt_manager, $root_path, $php_ext)
    {
        $this->user = $user;
        $this->request = $request;
        $this->language = $language;
        $this->alt_manager = $alt_manager;
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
    }

    public function handle($user_id)
    {
        $user_id = (int) $user_id;
        $current_user_id = (int) $this->user->data['user_id'];

        $hash = $this->request->variable('hash', '');
        if (!check_link_hash($hash, 'alt_switcher_switch_' . $user_id)) {
            trigger_error('FORM_INVALID');
        }

        if ($current_user_id === ANONYMOUS) {
            return new RedirectResponse(append_sid($this->root_path . 'ucp.' . $this->php_ext, 'mode=login'));
        }

        if ($user_id === $current_user_id) {
            return new RedirectResponse(append_sid($this->root_path . 'index.' . $this->php_ext));
        }

        $browser_id = $this->alt_manager->get_browser_id();
        $alt = $this->alt_manager->get_alt($browser_id, $user_id);
        if (!$alt) {
            trigger_error($this->language->lang('ALT_SWITCHER_UNKNOWN_ALT'));
        }

        $this->alt_manager->background_user($browser_id, $current_user_id);
        $this->alt_manager->remove_alt($browser_id, $user_id);

        $viewonline = $this->alt_manager->get_user_viewonline($user_id);

        $this->user->session_kill(false);
        $this->user->session_create($user_id, false, true, $viewonline);

        return new RedirectResponse(append_sid($this->root_path . 'index.' . $this->php_ext));
    }
}
?>
