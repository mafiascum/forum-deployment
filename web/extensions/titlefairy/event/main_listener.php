<?php

namespace mafiascum\titlefairy\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'core.permissions' => 'add_permission',
        );
    }

    public function add_permission($event)
    {
        $permissions = $event['permissions'];

        $permissions['a_titlefairy'] = array(
            'lang' => 'ACL_A_TITLEFAIRY',
            'cat'  => 'user_group',
        );

        $event['permissions'] = $permissions;
    }
}
