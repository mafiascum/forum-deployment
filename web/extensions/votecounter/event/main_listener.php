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

    protected $config;

    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $table_prefix, \phpbb\auth\auth $auth, \phpbb\user_loader $user_loader, \phpbb\config\config $config)
    {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
        $this->auth = $auth;
        $this->user_loader = $user_loader;
        $this->config = $config;
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

        $is_whitelisted = false;
        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        $is_whitelisted = $count > 0;

        if (!$is_whitelisted) {
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

    public function submit_post_end($event)
    {

        global $phpbb_root_path, $phpEx;

        $raw = $event->get_data();
        $data = $raw['data'] ?? [];

        if (!$data) {
            return;
        }

        $bot_user_id = 35786;

        if (($data['poster_id'] ?? 0) == $bot_user_id) {
            return;
        }

        $topic_id = (int) ($data['topic_id'] ?? 0);
        $forum_id = (int) ($data['forum_id'] ?? 0);
        $post_id = (int) ($data['post_id'] ?? 0);
        $topic_title = (string) ($data['topic_title'] ?? '');

        if (!$topic_id || !$forum_id || !$post_id || !$topic_title) {
            return;
        }

        $is_whitelisted = false;
        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        $is_whitelisted = $count > 0;
        if (!$is_whitelisted) {
            return;
        }

        $sql = 'SELECT COUNT(post_id) AS topic_post_number
                FROM ' . $this->table_prefix . 'posts
                WHERE topic_id = ' . $topic_id . '
                AND post_id <= ' . $post_id;

        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $post_number = (int) $this->db->sql_fetchfield('topic_post_number');
        $this->db->sql_freeresult($result);

        $posts_per_page = (int) $this->config['posts_per_page'] ?? 25;
        if ($post_number % $posts_per_page !== 0) {
            // return;
        }

        if (!function_exists('submit_post')) {
            include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        }

        if (!function_exists('generate_text_for_storage')) {
            include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);
        }

        $message = "Data dump:\n[code]" . print_r($data, true) . "[/code]";

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
