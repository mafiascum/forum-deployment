<?php

namespace mafiascum\votecounter\controller;

class postToTopic
{
    protected $request;
    protected $user;
    protected $auth;
    protected $db;

    public function __construct(
        \phpbb\request\request $request,
        \phpbb\user $user,
        \phpbb\auth\auth $auth,
        \phpbb\db\driver\driver_interface $db
    ) {
        $this->request = $request;
        $this->user = $user;
        $this->auth = $auth;
        $this->db = $db;
    }

    public function postAsBot($forum_id, $topic_id, $bot_user_id, $message)
    {
        global $phpbb_root_path, $phpEx;
        if (!function_exists('submit_post')) {
            include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        }

        if (!function_exists('generate_text_for_storage')) {
            include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);
        }

        $this->user->session_kill();
        $this->user->session_begin();
        $this->user->setup();
        $this->user->data = $this->user->data_loader->get_user_by_id($bot_user_id);

        $data = [
            'forum_id'      => $forum_id,
            'topic_id'      => $topic_id,
            'icon_id'       => false,
            'enable_bbcode' => true,
            'enable_smilies' => true,
            'enable_urls'   => true,
            'enable_sig'    => true,
            'message'       => $message,
            'message_md5'   => md5($message),
            'bbcode_bitfield' => '',
            'bbcode_uid'    => '',
            'post_edit_locked' => 0,
            'topic_title'   => '',
            'notify_set'    => false,
            'notify'        => false,
            'post_time'     => time(),
            'forum_name'    => '',
            'enable_indexing' => true,
        ];

        submit_post('post', $subject, '', POST_NORMAL, [], $data);
    }

    public function handle()
    {
        global $phpbb_root_path, $phpEx;

        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

        $topic_id = $this->request->variable('topic_id', 0);
        $user_id  = $this->request->variable('user_id', 0);
        $message  = $this->request->variable('message', '', true);

        $sql = 'SELECT *
                FROM ' . USERS_TABLE . '
                WHERE user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $posting_user = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$posting_user) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(['error' => 'User not found']);
        }

        $data = [
            'topic_id'      => $topic_id,
            'forum_id'      => 0, // must match topic forum
            'icon_id'       => 0,
            'enable_bbcode' => true,
            'enable_smilies' => true,
            'enable_urls'   => true,
            'enable_sig'    => true,
            'message'       => $message,
            'message_md5'   => md5($message),
            'bbcode_bitfield' => '',
            'bbcode_uid'    => '',
            'post_edit_locked' => 0,
            'topic_title'   => '',
            'poster_id'     => $user_id,
        ];

        submit_post(
            'reply',
            '',
            $posting_user['username'],
            POST_NORMAL,
            [],
            $data
        );

        return new \Symfony\Component\HttpFoundation\JsonResponse(['success' => true]);
    }
}
