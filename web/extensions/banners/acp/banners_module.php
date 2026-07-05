<?php

namespace mafiascum\banners\acp;

class banners_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $request, $db, $user, $phpbb_log, $table_prefix, $config;

        $user->add_lang_ext('mafiascum/banners', 'info_acp_banners');
        add_form_key('mafiascum_banners');

        $this->page_title = 'ACP_BANNERS_TITLE';

        $banners_table = $table_prefix . 'banners';
        $grants_table  = $table_prefix . 'banner_grants';
        $active_table  = $table_prefix . 'user_banners';

        $action    = $request->variable('action', '');
        $banner_id = (int) $request->variable('banner_id', 0);

        if ($action === 'toggle_enabled' && $request->is_set_post('submit')) {
            $this->handle_toggle_enabled($config, $user);
        }

        if ($action === 'create' && $request->is_set_post('submit')) {
            $this->handle_create($db, $user, $request, $banners_table);
        }

        if ($action === 'update' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_update($db, $user, $request, $banners_table, $banner_id);
        }

        if ($action === 'delete' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_delete($db, $user, $request, $banners_table, $grants_table, $active_table, $banner_id);
        }

        if ($action === 'add_grant' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_add_grant($db, $user, $request, $grants_table, $banner_id);
        }

        if ($action === 'remove_grant' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_remove_grant($db, $user, $request, $grants_table, $banner_id);
        }

        if ($action === 'add_active' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_add_active($db, $user, $request, $active_table, $banner_id);
        }

        if ($action === 'remove_active' && $request->is_set_post('submit') && $banner_id) {
            $this->handle_remove_active($db, $user, $request, $active_table, $banner_id);
        }

        if ($action === 'manage' && $banner_id) {
            $this->render_manage($db, $template, $banners_table, $grants_table, $active_table, $banner_id);
            return;
        }

        if ($action === 'delete_confirm' && $banner_id) {
            $this->render_delete_confirm($db, $template, $banners_table, $grants_table, $active_table, $banner_id);
            return;
        }

        $this->render_list($db, $template, $banners_table, $grants_table, $active_table);
    }

    private function render_list($db, $template, $banners_table, $grants_table, $active_table)
    {
        global $config;

        $this->tpl_name = 'acp_banners_list';

        $enabled = !empty($config['banners_enabled']);
        $template->assign_vars(array(
            'S_BANNERS_ENABLED'    => $enabled,
            'U_ACTION_TOGGLE'      => $this->u_action . '&amp;action=toggle_enabled',
        ));

        $sql = 'SELECT b.banner_id, b.banner_name, b.banner_image_url, b.is_public,
                    (SELECT COUNT(*) FROM ' . $active_table . ' WHERE banner_id = b.banner_id) AS active_count,
                    (SELECT COUNT(*) FROM ' . $grants_table . ' WHERE banner_id = b.banner_id) AS grant_count
                FROM ' . $banners_table . ' b
                ORDER BY b.banner_name ASC';
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $template->assign_block_vars('banners', array(
                'BANNER_ID'    => (int) $row['banner_id'],
                'BANNER_NAME'  => $row['banner_name'],
                'BANNER_URL'   => $row['banner_image_url'],
                'IS_PUBLIC'    => (int) $row['is_public'] === 1,
                'ACTIVE_COUNT' => (int) $row['active_count'],
                'GRANT_COUNT'  => (int) $row['grant_count'],
                'U_MANAGE'     => $this->u_action . '&amp;action=manage&amp;banner_id=' . (int) $row['banner_id'],
                'U_DELETE'     => $this->u_action . '&amp;action=delete_confirm&amp;banner_id=' . (int) $row['banner_id'],
            ));
        }
        $db->sql_freeresult($result);

        $template->assign_vars(array(
            'U_ACTION'        => $this->u_action,
            'U_ACTION_CREATE' => $this->u_action . '&amp;action=create',
        ));
    }

    private function render_manage($db, $template, $banners_table, $grants_table, $active_table, $banner_id)
    {
        global $request;

        $this->tpl_name = 'acp_banners_manage';

        $banner = $this->fetch_banner($db, $banners_table, $banner_id);
        if (!$banner) {
            trigger_error('BANNERS_NOT_FOUND' . adm_back_link($this->u_action), E_USER_WARNING);
        }

        $per_page = 10;
        $base_url = $this->u_action . '&amp;action=manage&amp;banner_id=' . (int) $banner_id;

        $grants_page = max(1, (int) $request->variable('grants_page', 1));
        $active_page = max(1, (int) $request->variable('active_page', 1));

        $sql = 'SELECT COUNT(*) AS c FROM ' . $grants_table . ' WHERE banner_id = ' . (int) $banner_id;
        $result = $db->sql_query($sql);
        $grants_total = (int) $db->sql_fetchfield('c');
        $db->sql_freeresult($result);

        $grants_pages = max(1, (int) ceil($grants_total / $per_page));
        if ($grants_page > $grants_pages) {
            $grants_page = $grants_pages;
        }
        $grants_offset = ($grants_page - 1) * $per_page;

        $sql = 'SELECT g.id, g.user_id, g.granted_by, g.created_at, u.username
                FROM ' . $grants_table . ' g
                JOIN ' . USERS_TABLE . ' u ON u.user_id = g.user_id
                WHERE g.banner_id = ' . (int) $banner_id . '
                ORDER BY u.username_clean ASC';
        $result = $db->sql_query_limit($sql, $per_page, $grants_offset);
        while ($row = $db->sql_fetchrow($result)) {
            $template->assign_block_vars('grants', array(
                'GRANT_ID' => (int) $row['id'],
                'USER_ID'  => (int) $row['user_id'],
                'USERNAME' => $row['username'],
            ));
        }
        $db->sql_freeresult($result);

        $sql = 'SELECT COUNT(*) AS c FROM ' . $active_table . ' WHERE banner_id = ' . (int) $banner_id;
        $result = $db->sql_query($sql);
        $active_total = (int) $db->sql_fetchfield('c');
        $db->sql_freeresult($result);

        $active_pages = max(1, (int) ceil($active_total / $per_page));
        if ($active_page > $active_pages) {
            $active_page = $active_pages;
        }
        $active_offset = ($active_page - 1) * $per_page;

        $sql = 'SELECT ub.id, ub.user_id, ub.slot, u.username
                FROM ' . $active_table . ' ub
                JOIN ' . USERS_TABLE . ' u ON u.user_id = ub.user_id
                WHERE ub.banner_id = ' . (int) $banner_id . '
                ORDER BY u.username_clean ASC, ub.slot ASC';
        $result = $db->sql_query_limit($sql, $per_page, $active_offset);
        while ($row = $db->sql_fetchrow($result)) {
            $template->assign_block_vars('active_users', array(
                'ACTIVE_ID' => (int) $row['id'],
                'USER_ID'   => (int) $row['user_id'],
                'USERNAME'  => $row['username'],
                'SLOT'      => (int) $row['slot'],
            ));
        }
        $db->sql_freeresult($result);

        $this->assign_pagination($template, 'grants_pagination', 'GRANTS', $base_url, 'grants_page', $grants_page, $grants_pages, 'active_page', $active_page);
        $this->assign_pagination($template, 'active_pagination', 'ACTIVE', $base_url, 'active_page', $active_page, $active_pages, 'grants_page', $grants_page);

        $template->assign_vars(array(
            'BANNER_ID'            => (int) $banner['banner_id'],
            'BANNER_NAME'          => $banner['banner_name'],
            'BANNER_URL'           => $banner['banner_image_url'],
            'IS_PUBLIC'            => (int) $banner['is_public'] === 1,
            'ACTIVE_COUNT'         => $active_total,
            'GRANTS_TOTAL'         => $grants_total,
            'GRANTS_PAGE'          => $grants_page,
            'GRANTS_PAGES'         => $grants_pages,
            'ACTIVE_PAGE'          => $active_page,
            'ACTIVE_PAGES'         => $active_pages,
            'U_BACK'               => $this->u_action,
            'U_ACTION_UPDATE'      => $this->u_action . '&amp;action=update&amp;banner_id=' . (int) $banner_id,
            'U_ACTION_ADD_GRANT'   => $this->u_action . '&amp;action=add_grant&amp;banner_id=' . (int) $banner_id,
            'U_ACTION_REM_GRANT'   => $this->u_action . '&amp;action=remove_grant&amp;banner_id=' . (int) $banner_id,
            'U_ACTION_ADD_ACTIVE'  => $this->u_action . '&amp;action=add_active&amp;banner_id=' . (int) $banner_id,
            'U_ACTION_REM_ACTIVE'  => $this->u_action . '&amp;action=remove_active&amp;banner_id=' . (int) $banner_id,
            'U_DELETE'             => $this->u_action . '&amp;action=delete_confirm&amp;banner_id=' . (int) $banner_id,
        ));
    }

    private function assign_pagination($template, $block, $prefix, $base_url, $page_param, $page, $pages, $other_param, $other_value)
    {
        $make_url = function ($n) use ($base_url, $page_param, $other_param, $other_value) {
            return $base_url . '&amp;' . $other_param . '=' . (int) $other_value . '&amp;' . $page_param . '=' . (int) $n;
        };

        $prev_page = $page > 1 ? $page - 1 : 0;
        $next_page = $page < $pages ? $page + 1 : 0;

        if ($pages > 1) {
            for ($n = 1; $n <= $pages; $n++) {
                $template->assign_block_vars($block, array(
                    'NUMBER'  => $n,
                    'URL'     => $make_url($n),
                    'CURRENT' => $n === $page,
                ));
            }
        }

        $template->assign_vars(array(
            'S_' . $prefix . '_HAS_PAGINATION' => $pages > 1,
            'U_' . $prefix . '_PREV'           => $prev_page ? $make_url($prev_page) : '',
            'U_' . $prefix . '_NEXT'           => $next_page ? $make_url($next_page) : '',
        ));
    }

    private function render_delete_confirm($db, $template, $banners_table, $grants_table, $active_table, $banner_id)
    {
        $this->tpl_name = 'acp_banners_delete';

        $banner = $this->fetch_banner($db, $banners_table, $banner_id);
        if (!$banner) {
            trigger_error('BANNERS_NOT_FOUND' . adm_back_link($this->u_action), E_USER_WARNING);
        }

        $sql = 'SELECT
                    (SELECT COUNT(*) FROM ' . $active_table . " WHERE banner_id = " . (int) $banner_id . ") AS active_count,
                    (SELECT COUNT(*) FROM " . $grants_table . " WHERE banner_id = " . (int) $banner_id . ") AS grant_count";
        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        $template->assign_vars(array(
            'BANNER_ID'       => (int) $banner['banner_id'],
            'BANNER_NAME'     => $banner['banner_name'],
            'BANNER_URL'      => $banner['banner_image_url'],
            'ACTIVE_COUNT'    => (int) $row['active_count'],
            'GRANT_COUNT'     => (int) $row['grant_count'],
            'U_BACK'          => $this->u_action,
            'U_ACTION_DELETE' => $this->u_action . '&amp;action=delete&amp;banner_id=' . (int) $banner_id,
        ));
    }

    private function handle_toggle_enabled($config, $user)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }
        $new_value = empty($config['banners_enabled']) ? 1 : 0;
        $config->set('banners_enabled', $new_value);
        $msg_key = $new_value ? 'BANNERS_ENABLED_ON' : 'BANNERS_ENABLED_OFF';
        trigger_error($user->lang[$msg_key] . adm_back_link($this->u_action));
    }

    private function handle_create($db, $user, $request, $banners_table)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $name  = trim((string) $request->variable('banner_name', '', true));
        $url   = trim((string) $request->variable('banner_url', ''));
        $pub   = $request->variable('is_public', 0) ? 1 : 0;

        if ($name === '' || $url === '') {
            trigger_error($user->lang['BANNERS_ERR_REQUIRED'] . adm_back_link($this->u_action), E_USER_WARNING);
        }

        $sql = 'INSERT INTO ' . $banners_table . ' ' . $db->sql_build_array('INSERT', array(
            'banner_name'      => $name,
            'banner_image_url' => $url,
            'is_public'        => $pub,
        ));
        $db->sql_query($sql);

        trigger_error($user->lang['BANNERS_CREATED'] . adm_back_link($this->u_action));
    }

    private function handle_update($db, $user, $request, $banners_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $name = trim((string) $request->variable('banner_name', '', true));
        $url  = trim((string) $request->variable('banner_url', ''));
        $pub  = $request->variable('is_public', 0) ? 1 : 0;

        if ($name === '' || $url === '') {
            trigger_error($user->lang['BANNERS_ERR_REQUIRED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $sql = 'UPDATE ' . $banners_table . ' SET ' . $db->sql_build_array('UPDATE', array(
            'banner_name'      => $name,
            'banner_image_url' => $url,
            'is_public'        => $pub,
        )) . ' WHERE banner_id = ' . (int) $banner_id;
        $db->sql_query($sql);

        trigger_error($user->lang['BANNERS_UPDATED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id));
    }

    private function handle_delete($db, $user, $request, $banners_table, $grants_table, $active_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $banner = $this->fetch_banner($db, $banners_table, $banner_id);
        if (!$banner) {
            trigger_error('BANNERS_NOT_FOUND' . adm_back_link($this->u_action), E_USER_WARNING);
        }

        $typed = trim((string) $request->variable('confirm_name', '', true));
        if ($typed !== $banner['banner_name']) {
            trigger_error($user->lang['BANNERS_ERR_NAME_MISMATCH'] . adm_back_link($this->u_action . '&amp;action=delete_confirm&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $db->sql_query('DELETE FROM ' . $active_table . ' WHERE banner_id = ' . (int) $banner_id);
        $db->sql_query('DELETE FROM ' . $grants_table . ' WHERE banner_id = ' . (int) $banner_id);
        $db->sql_query('DELETE FROM ' . $banners_table . ' WHERE banner_id = ' . (int) $banner_id);

        trigger_error($user->lang['BANNERS_DELETED'] . adm_back_link($this->u_action));
    }

    private function handle_add_grant($db, $user, $request, $grants_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $username = trim((string) $request->variable('grant_username', '', true));
        if ($username === '') {
            trigger_error($user->lang['BANNERS_ERR_USER_REQUIRED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $sql = 'SELECT user_id FROM ' . USERS_TABLE . "
                WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
        $result = $db->sql_query_limit($sql, 1);
        $target = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$target) {
            trigger_error(sprintf($user->lang['BANNERS_ERR_USER_NOT_FOUND'], $username) . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $sql = 'SELECT id FROM ' . $grants_table . ' WHERE banner_id = ' . (int) $banner_id . ' AND user_id = ' . (int) $target['user_id'];
        $result = $db->sql_query_limit($sql, 1);
        $exists = (bool) $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$exists) {
            $sql = 'INSERT INTO ' . $grants_table . ' ' . $db->sql_build_array('INSERT', array(
                'banner_id'  => (int) $banner_id,
                'user_id'    => (int) $target['user_id'],
                'granted_by' => (int) $user->data['user_id'],
                'created_at' => time(),
            ));
            $db->sql_query($sql);
        }

        trigger_error($user->lang['BANNERS_GRANT_ADDED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id));
    }

    private function handle_remove_grant($db, $user, $request, $grants_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $grant_id = (int) $request->variable('grant_id', 0);
        if (!$grant_id) {
            trigger_error($user->lang['BANNERS_ERR_INVALID'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $db->sql_query('DELETE FROM ' . $grants_table . ' WHERE id = ' . $grant_id . ' AND banner_id = ' . (int) $banner_id);

        trigger_error($user->lang['BANNERS_GRANT_REMOVED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id));
    }

    private function handle_add_active($db, $user, $request, $active_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $username = trim((string) $request->variable('active_username', '', true));
        if ($username === '') {
            trigger_error($user->lang['BANNERS_ERR_USER_REQUIRED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $sql = 'SELECT user_id FROM ' . USERS_TABLE . "
                WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
        $result = $db->sql_query_limit($sql, 1);
        $target = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$target) {
            trigger_error(sprintf($user->lang['BANNERS_ERR_USER_NOT_FOUND'], $username) . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $target_id = (int) $target['user_id'];

        $sql = 'SELECT slot FROM ' . $active_table . '
                WHERE user_id = ' . $target_id . '
                ORDER BY slot ASC';
        $result = $db->sql_query($sql);
        $taken_slots = array();
        $already_has = false;
        while ($row = $db->sql_fetchrow($result)) {
            $taken_slots[(int) $row['slot']] = true;
        }
        $db->sql_freeresult($result);

        $sql = 'SELECT id FROM ' . $active_table . '
                WHERE user_id = ' . $target_id . '
                AND banner_id = ' . (int) $banner_id;
        $result = $db->sql_query_limit($sql, 1);
        $already_has = (bool) $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if ($already_has) {
            trigger_error($user->lang['BANNERS_ERR_USER_HAS_BANNER'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $slot = 0;
        for ($i = 1; $i <= 3; $i++) {
            if (empty($taken_slots[$i])) {
                $slot = $i;
                break;
            }
        }

        if ($slot === 0) {
            trigger_error($user->lang['BANNERS_ERR_SLOTS_FULL'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $sql = 'INSERT INTO ' . $active_table . ' ' . $db->sql_build_array('INSERT', array(
            'user_id'   => $target_id,
            'banner_id' => (int) $banner_id,
            'slot'      => $slot,
        ));
        $db->sql_query($sql);

        trigger_error($user->lang['BANNERS_ACTIVE_ADDED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id));
    }

    private function handle_remove_active($db, $user, $request, $active_table, $banner_id)
    {
        if (!check_form_key('mafiascum_banners')) {
            trigger_error('FORM_INVALID', E_USER_WARNING);
        }

        $active_id = (int) $request->variable('active_id', 0);
        if (!$active_id) {
            trigger_error($user->lang['BANNERS_ERR_INVALID'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id), E_USER_WARNING);
        }

        $db->sql_query('DELETE FROM ' . $active_table . ' WHERE id = ' . $active_id . ' AND banner_id = ' . (int) $banner_id);

        trigger_error($user->lang['BANNERS_ACTIVE_REMOVED'] . adm_back_link($this->u_action . '&amp;action=manage&amp;banner_id=' . $banner_id));
    }

    private function fetch_banner($db, $banners_table, $banner_id)
    {
        $sql = 'SELECT banner_id, banner_name, banner_image_url, is_public
                FROM ' . $banners_table . '
                WHERE banner_id = ' . (int) $banner_id;
        $result = $db->sql_query_limit($sql, 1);
        $row = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);
        return $row;
    }
}
