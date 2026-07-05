<?php

namespace mafiascum\banners\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;

    /** @var \phpbb\config\config */
    protected $config;

    /** @var string */
    protected $table_prefix;

    /** @var array<int,string> */
    private $banner_cache = [];

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $table_prefix)
    {
        $this->db = $db;
        $this->config = $config;
        $this->table_prefix = $table_prefix;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'core.viewtopic_modify_post_row' => 'add_banner_to_post_row',
            'core.permissions'               => 'add_permission',
            'core.user_setup'                => 'load_language_on_setup',
        );
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'mafiascum/banners',
            'lang_set' => 'ucp_banners',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    public function add_banner_to_post_row($event)
    {
        if (empty($this->config['banners_enabled'])) {
            return;
        }
        $post_row = $event['post_row'];
        $post_row['BANNER'] = $this->get_banner((int) $event['row']['user_id']);
        $event['post_row'] = $post_row;
    }

    public function add_permission($event)
    {
        $permissions = $event['permissions'];
        $permissions['a_banners'] = array(
            'lang' => 'ACL_A_BANNERS',
            'cat'  => 'user_group',
        );
        $event['permissions'] = $permissions;
    }

    private function get_banner($user_id)
    {
        if (isset($this->banner_cache[$user_id])) {
            return $this->banner_cache[$user_id];
        }

        $sql = 'SELECT b.banner_image_url, b.banner_name
                FROM ' . $this->table_prefix . 'user_banners ub
                JOIN ' . $this->table_prefix . 'banners b ON ub.banner_id = b.banner_id
                WHERE ub.user_id = ' . (int) $user_id . '
                ORDER BY ub.slot ASC';
        $result = $this->db->sql_query_limit($sql, 3);

        $parts = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $url = htmlspecialchars((string) $row['banner_image_url'], ENT_QUOTES);
            $name = htmlspecialchars((string) $row['banner_name'], ENT_QUOTES);
            $parts[] = '<img src="' . $url . '" alt="' . $name . '" title="' . $name . '">';
        }
        $this->db->sql_freeresult($result);

        $html = implode('<br>', $parts);
        $this->banner_cache[$user_id] = $html;
        return $html;
    }
}
