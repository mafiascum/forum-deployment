<?php

namespace mafiascum\banners\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'core.viewtopic_modify_post_row' => 'add_banner_to_post_row',
        );
    }

    public function add_banner_to_post_row($event)
    {
        $post_row = $event['post_row'];
        $post_row['BANNER'] = $this->get_banner((int) $event['row']['user_id']);
        $event['post_row'] = $post_row;
    }

    private function get_banner($user_id)
    {
        return '<img src="https://forum.mafiascum.net/images/banners/pridebi1.png" style="color: rgb(153, 153, 153);">';
    }
}
