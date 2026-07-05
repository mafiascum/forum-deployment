<?php

namespace mafiascum\banners\ucp;

class banners_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $request, $db, $user, $table_prefix, $phpbb_root_path, $phpEx;

        $user->add_lang_ext('mafiascum/banners', 'ucp_banners');
        add_form_key('mafiascum_ucp_banners');

        $this->tpl_name   = 'ucp_banners';
        $this->page_title = 'UCP_BANNERS';

        $banners_table = $table_prefix . 'banners';
        $grants_table  = $table_prefix . 'banner_grants';
        $active_table  = $table_prefix . 'user_banners';
        $user_id       = (int) $user->data['user_id'];

        $action    = $request->variable('action', '');
        $banner_id = (int) $request->variable('banner_id', 0);

        if ($request->is_set_post('submit') && $action) {
            if (!check_form_key('mafiascum_ucp_banners')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $allowed_ids = $this->get_allowed_ids($db, $banners_table, $grants_table, $user_id);
            $equipped_ids = $this->get_equipped_ids($db, $active_table, $user_id);

            if ($action === 'add') {
                $this->do_add($user, $banner_id, $allowed_ids, $equipped_ids);
            } elseif ($action === 'remove') {
                $this->do_remove($user, $banner_id, $equipped_ids);
            } elseif ($action === 'move_up') {
                $this->do_move($user, $banner_id, $equipped_ids, -1);
            } elseif ($action === 'move_down') {
                $this->do_move($user, $banner_id, $equipped_ids, +1);
            }

            $this->persist_order($db, $active_table, $user_id, $equipped_ids);

            redirect($this->u_action);
        }

        $available = $this->fetch_available_banners($db, $banners_table, $grants_table, $user_id);
        $equipped_ids = $this->get_equipped_ids($db, $active_table, $user_id);

        $by_id = array();
        foreach ($available as $b) {
            $by_id[(int) $b['banner_id']] = $b;
        }

        $equipped_count = count($equipped_ids);
        $last_index     = $equipped_count - 1;

        foreach ($equipped_ids as $i => $bid) {
            if (!isset($by_id[$bid])) {
                continue;
            }
            $b = $by_id[$bid];
            $template->assign_block_vars('equipped', array(
                'BANNER_ID'   => $bid,
                'BANNER_NAME' => $b['banner_name'],
                'BANNER_URL'  => $b['banner_image_url'],
                'SLOT'        => $i + 1,
                'IS_FIRST'    => $i === 0,
                'IS_LAST'     => $i === $last_index,
            ));
        }

        $equipped_set = array_flip($equipped_ids);
        $has_unequipped = false;
        foreach ($available as $b) {
            $bid = (int) $b['banner_id'];
            if (isset($equipped_set[$bid])) {
                continue;
            }
            $has_unequipped = true;
            $template->assign_block_vars('unequipped', array(
                'BANNER_ID'   => $bid,
                'BANNER_NAME' => $b['banner_name'],
            ));
        }

        $template->assign_vars(array(
            'S_HAS_AVAILABLE' => !empty($available),
            'S_HAS_EQUIPPED'  => $equipped_count > 0,
            'S_CAN_ADD'       => $equipped_count < 3 && $has_unequipped,
            'S_AT_LIMIT'      => $equipped_count >= 3,
            'EQUIPPED_COUNT'  => $equipped_count,
            'MAX_BANNERS'     => 3,
            'U_ACTION'        => $this->u_action,
        ));
    }

    private function do_add($user, $banner_id, array $allowed_ids, array &$equipped_ids)
    {
        if ($banner_id <= 0) {
            $this->fail($user->lang['UCP_BANNERS_ERR_INVALID']);
        }
        if (count($equipped_ids) >= 3) {
            $this->fail($user->lang['UCP_BANNERS_ERR_LIMIT']);
        }
        if (!in_array($banner_id, $allowed_ids, true)) {
            $this->fail($user->lang['UCP_BANNERS_ERR_NOT_ALLOWED']);
        }
        if (in_array($banner_id, $equipped_ids, true)) {
            $this->fail($user->lang['UCP_BANNERS_ERR_ALREADY_EQUIPPED']);
        }
        $equipped_ids[] = $banner_id;
    }

    private function do_remove($user, $banner_id, array &$equipped_ids)
    {
        $index = array_search($banner_id, $equipped_ids, true);
        if ($index === false) {
            $this->fail($user->lang['UCP_BANNERS_ERR_INVALID']);
        }
        array_splice($equipped_ids, $index, 1);
    }

    private function do_move($user, $banner_id, array &$equipped_ids, $delta)
    {
        $index = array_search($banner_id, $equipped_ids, true);
        if ($index === false) {
            $this->fail($user->lang['UCP_BANNERS_ERR_INVALID']);
        }
        $target = $index + $delta;
        if ($target < 0 || $target >= count($equipped_ids)) {
            return;
        }
        $tmp = $equipped_ids[$index];
        $equipped_ids[$index] = $equipped_ids[$target];
        $equipped_ids[$target] = $tmp;
    }

    private function persist_order($db, $active_table, $user_id, array $ordered_ids)
    {
        $db->sql_query('DELETE FROM ' . $active_table . ' WHERE user_id = ' . (int) $user_id);
        $slot = 0;
        foreach ($ordered_ids as $bid) {
            $slot++;
            if ($slot > 3) {
                break;
            }
            $sql = 'INSERT INTO ' . $active_table . ' ' . $db->sql_build_array('INSERT', array(
                'user_id'   => (int) $user_id,
                'banner_id' => (int) $bid,
                'slot'      => $slot,
            ));
            $db->sql_query($sql);
        }
    }

    private function get_equipped_ids($db, $active_table, $user_id)
    {
        $sql = 'SELECT banner_id, slot FROM ' . $active_table . '
                WHERE user_id = ' . (int) $user_id . '
                ORDER BY slot ASC';
        $result = $db->sql_query($sql);
        $out = array();
        while ($row = $db->sql_fetchrow($result)) {
            $out[] = (int) $row['banner_id'];
        }
        $db->sql_freeresult($result);
        return $out;
    }

    private function get_allowed_ids($db, $banners_table, $grants_table, $user_id)
    {
        $rows = $this->fetch_available_banners($db, $banners_table, $grants_table, $user_id);
        return array_map(function ($r) { return (int) $r['banner_id']; }, $rows);
    }

    private function fetch_available_banners($db, $banners_table, $grants_table, $user_id)
    {
        $sql = 'SELECT b.banner_id, b.banner_name, b.banner_image_url
                FROM ' . $banners_table . ' b
                LEFT JOIN ' . $grants_table . ' g
                    ON g.banner_id = b.banner_id AND g.user_id = ' . (int) $user_id . '
                WHERE b.is_public = 1 OR g.user_id IS NOT NULL
                GROUP BY b.banner_id, b.banner_name, b.banner_image_url, b.is_public
                ORDER BY b.banner_name ASC';
        $result = $db->sql_query($sql);
        $out = array();
        while ($row = $db->sql_fetchrow($result)) {
            $out[] = $row;
        }
        $db->sql_freeresult($result);
        return $out;
    }

    private function fail($message)
    {
        trigger_error($message . '<br /><br /><a href="' . $this->u_action . '">Return</a>', E_USER_WARNING);
    }
}
