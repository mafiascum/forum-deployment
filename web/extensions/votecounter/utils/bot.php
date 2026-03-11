<?php

namespace mafiascum\votecounter\utils;

class BotPoster
{
    public static function postMessage(
        int $bot_user_id,
        int $forum_id,
        int $topic_id,
        string $message,
        string $topic_title,
        \phpbb\user $user,
        \phpbb\user_loader $user_loader
    ) {
        global $phpbb_root_path, $phpEx;

        if (!function_exists('submit_post')) {
            include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        }
        if (!function_exists('generate_text_for_storage')) {
            include_once($phpbb_root_path . 'includes/functions_content.' . $phpEx);
        }

        $user_loader->load_users([$bot_user_id]);
        $bot_user = $user_loader->get_user($bot_user_id);
        if (!$bot_user) {
            return false;
        }

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

        $post_data = [
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
            'topic_title'     => $topic_title,
        ];

        $original_user_data = $user->data;
        $user->data['user_id']        = $bot_user['user_id'];
        $user->data['username']       = $bot_user['username'];
        $user->data['username_clean'] = $bot_user['username_clean'];
        $user->data['user_colour']    = $bot_user['user_colour'];

        submit_post(
            'reply',
            $topic_title,
            $bot_user['username'],
            POST_NORMAL,
            $poll,
            $post_data
        );

        $user->data = $original_user_data;

        return true;
    }
}
