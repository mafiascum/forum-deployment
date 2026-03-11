<?php

namespace mafiascum\votecounter\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

        $topic_id = $data['topic_id'];
        $forum_id = $data['forum_id'];
        $poster_id = $data['poster_id'];
        $bot_user_id = 3467;

        if ($poster_id == $bot_user_id) {
            return;
        }


        $message = "Bot response.";

        $poll = [];
        $uid = $bitfield = $options = '';

        generate_text_for_storage(
            $message,
            $uid,
            $bitfield,
            $options,
            true,
            true,
            true
        );


        $bot_user = $this->user_loader->get_user($bot_user_id);
        if (!$bot_user) {
            return;
        }

        $bot_post_data = [
            'forum_id'        => $forum_id,
            'topic_id'        => $topic_id,
            'poster_id'       => $bot_user_id,
            'icon_id'         => 0,
            'enable_bbcode'   => true,
            'enable_smilies'  => true,
            'enable_urls'     => true,
            'enable_sig'      => false,
            'message'         => $message,
            'message_md5'     => md5($message),
            'bbcode_bitfield' => $bitfield,
            'bbcode_uid'      => $uid,
            'post_edit_locked' => 0,
            'topic_title'     => $data['topic_title'],
        ];


        submit_post(
            'reply',
            $data['topic_title'],
            '',
            POST_NORMAL,
            $poll,
            $bot_post_data
        );
    }
}
