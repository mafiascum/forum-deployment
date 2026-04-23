<?php

namespace mafiascum\bbcodes\notification\type;

class mention extends \phpbb\notification\type\base
{
    /** @var \phpbb\user_loader */
    protected $user_loader;

    public function set_user_loader(\phpbb\user_loader $user_loader)
    {
        $this->user_loader = $user_loader;
    }

    public function get_type()
    {
        return 'notification.type.mention';
    }

    static public function get_item_id($data)
    {
        return (int) $data['post_id'];
    }

    static public function get_item_parent_id($data)
    {
        return (int) $data['topic_id'];
    }

    public function find_users_for_notification($data, $options = [])
    {
        return [];
    }

    public function users_to_query()
    {
        return [$this->get_data('poster_id')];
    }

    public function get_avatar()
    {
        return $this->user_loader->get_avatar($this->get_data('poster_id'), false, true);
    }

    public function get_title()
    {
        $this->language->add_lang('common', 'mafiascum/bbcodes');
        return $this->language->lang(
            'NOTIFICATION_MENTION',
            $this->get_data('post_username'),
            $this->get_data('topic_title')
        );
    }

    public function get_url()
    {
        return append_sid(
            generate_board_url() . '/viewtopic.php',
            'p=' . $this->item_id . '#p' . $this->item_id
        );
    }

    public function get_email_template()
    {
        return false;
    }

    public function get_email_template_variables()
    {
        return [];
    }

    public function create_insert_array($data, $pre_create_data = [])
    {
        $this->notification_data = array_merge($this->notification_data, [
            'poster_id'     => (int) $data['poster_id'],
            'topic_title'   => $data['topic_title'],
            'post_subject'  => $data['post_subject'],
            'post_username' => $data['post_username'],
            'forum_id'      => (int) $data['forum_id'],
            'forum_name'    => $data['forum_name'],
        ]);
        parent::create_insert_array($data, $pre_create_data);
    }
}
