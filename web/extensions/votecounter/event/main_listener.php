<?php

namespace mafiascum\votecounter\event;

require_once(dirname(__FILE__) . "/../utils/bot.php");

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use mafiascum\votecounter\utils\BotPoster;

class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\auth\auth */
    protected $auth;

    protected $user_loader;

    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $table_prefix, \phpbb\auth\auth $auth, \phpbb\user_loader $user_loader)
    {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
        $this->auth = $auth;
        $this->user_loader = $user_loader;
    }


    static public function getSubscribedEvents()
    {
        return array(
            'core.posting_modify_template_vars' => 'add_votecounter_panel',
            'core.submit_post_end' => 'submit_post_end'
        );
    }

    public function add_votecounter_panel($event)
    {
        $this->user->add_lang_ext('mafiascum/votecounter', 'votecounter');

        $event_data = $event->get_data();
        $forum_id = $event_data['forum_id'];
        $topic_id = $event_data['topic_id'];

        if (!$forum_id) {
            $this->template->assign_vars([
                'S_VOTECOUNTER_PANEL' => false,
            ]);
            return;
        }

        $has_auth = $this->auth->acl_get('m_edit', $forum_id);
        $this->template->assign_vars([
            'S_VOTECOUNTER_PANEL' => $has_auth,
            'VC_TOPIC_ID' => $topic_id,
        ]);
    }

    public function submit_post_before($event)
    {
        $post_data = $event['post_data'];
        $data = $event['data'];
        $post_mode = $event['mode'];

        if ($post_mode == 'post') {
            $post_data['text'] = 'Overridden';
        }

        $event['post_data'] = $post_data;
    }

    public function submit_post_end($event)
    {
        global $phpbb_root_path, $phpEx;
        if (!function_exists('submit_post')) {
            include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        }

        if (!function_exists('generate_text_for_storage')) {
            include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);
        }

        $data = $event['data'];

        $topic_id = (int) $data['topic_id'];
        $forum_id = (int) $data['forum_id'];
        $poster_id = (int) $data['poster_id'];
        $bot_user_id = 3467;

        if ($poster_id == $bot_user_id) {
            return;
        }

        $topic_title = (string) $data['topic_title'];
        $message = '[b][u]Test[/u][/b]';

        if (!$topic_title) {
            return;
        }

        BotPoster::postMessage(
            $bot_user_id,
            $forum_id,
            $topic_id,
            $message,
            $topic_title,
            $this->user,
            $this->user_loader
        );
    }
}
