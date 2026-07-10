<?php

namespace mafiascum\alt_switcher\event;

use mafiascum\alt_switcher\includes\AltSessionManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    protected $config;
    protected $template;
    protected $request;
    protected $user;
    protected $user_loader;
    protected $language;
    protected $helper;
    protected $alt_manager;
    protected $root_path;
    protected $php_ext;

    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup'         => 'load_language_on_setup',
            'core.user_setup_after'   => 'ensure_browser_cookie',
            'core.page_header_after'  => 'inject_template_vars',
        );
    }

    public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\controller\helper $helper, AltSessionManager $alt_manager, $root_path, $php_ext)
    {
        $this->config = $config;
        $this->template = $template;
        $this->request = $request;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->helper = $helper;
        $this->alt_manager = $alt_manager;
        $this->root_path = $root_path;
        $this->php_ext = $php_ext;
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'mafiascum/alt_switcher',
            'lang_set' => 'common',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function ensure_browser_cookie($event)
    {
        if ((int) $this->user->data['user_id'] === ANONYMOUS) {
            return;
        }
        $this->alt_manager->ensure_browser_id();
    }

    public function inject_template_vars($event)
    {
        $current_user_id = (int) $this->user->data['user_id'];
        if ($current_user_id === ANONYMOUS) {
            return;
        }

        $browser_id = $this->alt_manager->get_browser_id();
        $alts = $browser_id === '' ? array() : $this->alt_manager->get_alts($browser_id);

        $user_ids = array();
        foreach ($alts as $alt) {
            if ((int) $alt['user_id'] !== $current_user_id) {
                $user_ids[] = (int) $alt['user_id'];
            }
        }
        if (!empty($user_ids)) {
            $this->user_loader->load_users($user_ids);
        }

        foreach ($alts as $alt) {
            $alt_user_id = (int) $alt['user_id'];
            if ($alt_user_id === $current_user_id) {
                continue;
            }
            $username = $this->user_loader->get_username($alt_user_id, 'no_profile');
            if ($username === '' || $username === null) {
                continue;
            }
            $this->template->assign_block_vars('alt_accounts', array(
                'USER_ID'    => $alt_user_id,
                'USERNAME'   => $username,
                'AVATAR'     => $this->user_loader->get_avatar($alt_user_id, false, true),
                'SWITCH_URL' => $this->helper->route('alt_switcher_switch_route', array(
                    'user_id' => $alt_user_id,
                    'hash'    => generate_link_hash('alt_switcher_switch_' . $alt_user_id),
                )),
            ));
        }

        $has_alts = !empty($user_ids);

        $this->template->assign_vars(array(
            'S_ALT_SWITCHER_ENABLED'        => true,
            'S_ALT_SWITCHER_HAS_ALTS'       => $has_alts,
            'ALT_SWITCHER_URL_ADD'          => $this->helper->route('alt_switcher_add_route', array('hash' => generate_link_hash('alt_switcher_add'))),
            'ALT_SWITCHER_URL_LOGOUT_ALL'   => $this->helper->route('alt_switcher_logout_all_route', array('hash' => generate_link_hash('alt_switcher_logout_all'))),
            'S_ALT_SWITCHER_ADDING_ACCOUNT' => (bool) $this->request->variable('alt_switcher_adding', 0),
            'U_LOGIN_LOGOUT'                => $this->helper->route('alt_switcher_logout_current_route', array('hash' => generate_link_hash('alt_switcher_logout_current'))),
        ));
    }
}
?>
