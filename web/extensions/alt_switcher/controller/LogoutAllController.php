<?php

namespace mafiascum\alt_switcher\controller;

use mafiascum\alt_switcher\includes\AltSessionManager;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutAllController
{
    protected $user;
    protected $request;
    protected $alt_manager;
    protected $root_path;
    protected $php_ext;

    public function __construct(\phpbb\user $user, \phpbb\request\request_interface $request, AltSessionManager $alt_manager, $root_path, $php_ext)
    {
        $this->user = $user;
        $this->request = $request;
        $this->alt_manager = $alt_manager;
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
    }

    public function handle()
    {
        $hash = $this->request->variable('hash', '');
        if (!check_link_hash($hash, 'alt_switcher_logout_all')) {
            trigger_error('FORM_INVALID');
        }

        $browser_id = $this->alt_manager->get_browser_id();
        if ($browser_id !== '') {
            $this->alt_manager->wipe_browser($browser_id);
            $this->alt_manager->clear_browser_cookie();
        }

        if ((int) $this->user->data['user_id'] !== ANONYMOUS) {
            $this->user->session_kill(false);
        }

        return new RedirectResponse(append_sid($this->root_path . 'index.' . $this->php_ext));
    }
}
?>
