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
    //protected $auth;

    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $table_prefix)
    {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
        //$this->auth = $auth;
    }


    static public function getSubscribedEvents()
    {
        return array(
            'core.posting_modify_template_vars' => 'add_votecounter_panel'
        );
    }

    public function add_votecounter_panel($event)
    {

        $this->user->add_lang_ext('mafiascum/votecounter', 'votecounter');
        $topic_id = (int) $event['topic_id'] ?? 0;

        $this->template->assign_vars([
            'S_VOTECOUNTER_PANEL' => true,
        ]);
    }
}
