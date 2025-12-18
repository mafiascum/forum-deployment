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

    public function __construct(
        \phpbb\template\template $template,
        \phpbb\user $user,
        \phpbb\db\driver\driver_interface $db,
        $table_prefix
    ) {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
    }


    static public function getSubscribedEvents()
    {
        return array(
            'core.posting_modify_template_vars' => 'add_votecounter_panel',
            'core.page_footer' => 'add_js'
        );
    }

    public function add_js($event)
    {
        $this->template->assign_var(
            'SCRIPTS',
            '@mafiascum_votecounter/votecounter_settings.js'
        );
    }

    private function is_permitted($topic_id)
    {
        if (!$topic_id) {
            return false;
        }

        $current_user_id = (int) $this->user->data['user_id'];

        $sql = 'SELECT topic_poster FROM ' . $this->table_prefix . 'topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $topic_poster = $row ? (int) $row['topic_poster'] : 0;
        $this->db->sql_freeresult($result);

        $sql = 'SELECT user_id FROM ' . $this->table_prefix . 'topic_mod WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $topic_mods = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $topic_mods[] = (int) $row['user_id'];
        }
        $this->db->sql_freeresult($result);

        return $current_user_id === $topic_poster || in_array($current_user_id, $topic_mods);
    }

    public function add_votecounter_panel($event)
    {
        $this->user->add_lang_ext('mafiascum/votecounter', 'votecounter');

        $topic_id = (int) $event['topic_id'] ?? 0;
        $show_panel = $this->is_permitted($topic_id);

        $this->template->assign_vars([
            'S_VOTECOUNTER_PANEL' => $show_panel,
        ]);
    }
}
