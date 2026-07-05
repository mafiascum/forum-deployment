<?php

namespace mafiascum\titlefairy\acp;

class titlefairy_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $request, $db, $user, $phpbb_log;

        $this->tpl_name   = 'acp_titlefairy_body';
        $this->page_title = 'ACP_TITLEFAIRY_TITLE';

        $user->add_lang_ext('mafiascum/titlefairy', 'info_acp_titlefairy');

        add_form_key('mafiascum_titlefairy');

        $action        = $request->variable('action', '');
        $username      = $request->variable('username', '', true);
        $selected_user = null;

        if ($username !== '') {
            $sql = 'SELECT user_id, username, user_rank
                    FROM ' . USERS_TABLE . "
                    WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
            $result = $db->sql_query_limit($sql, 1);
            $selected_user = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if (!$selected_user) {
                trigger_error(
                    sprintf($user->lang['TITLEFAIRY_USER_NOT_FOUND'], $username)
                    . adm_back_link($this->u_action),
                    E_USER_WARNING
                );
            }
        }

        if ($action === 'assign' && $request->is_set_post('submit') && $selected_user) {
            if (!check_form_key('mafiascum_titlefairy')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $rank_id = (int) $request->variable('rank_id', 0);

            if ($rank_id !== 0) {
                $sql = 'SELECT rank_id
                        FROM ' . RANKS_TABLE . '
                        WHERE rank_id = ' . $rank_id . '
                            AND rank_special = 1';
                $result      = $db->sql_query_limit($sql, 1);
                $rank_exists = (bool) $db->sql_fetchrow($result);
                $db->sql_freeresult($result);

                if (!$rank_exists) {
                    trigger_error(
                        $user->lang['TITLEFAIRY_RANK_NOT_FOUND']
                        . adm_back_link($this->u_action),
                        E_USER_WARNING
                    );
                }
            }

            $sql = 'UPDATE ' . USERS_TABLE . '
                    SET user_rank = ' . $rank_id . '
                    WHERE user_id = ' . (int) $selected_user['user_id'];
            $db->sql_query($sql);

            $phpbb_log->add(
                'admin',
                (int) $user->data['user_id'],
                $user->ip,
                'LOG_TITLEFAIRY_RANK_ASSIGNED',
                false,
                array($selected_user['username'], $rank_id)
            );

            trigger_error(
                $user->lang['TITLEFAIRY_RANK_ASSIGNED']
                . adm_back_link($this->u_action . '&amp;username=' . urlencode($selected_user['username']))
            );
        }

        $sql = 'SELECT rank_id, rank_title
                FROM ' . RANKS_TABLE . '
                WHERE rank_special = 1
                ORDER BY rank_title ASC';
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $template->assign_block_vars('ranks', array(
                'RANK_ID'    => (int) $row['rank_id'],
                'RANK_TITLE' => $row['rank_title'],
                'S_SELECTED' => $selected_user && (int) $selected_user['user_rank'] === (int) $row['rank_id'],
            ));
        }
        $db->sql_freeresult($result);

        if ($selected_user) {
            $current_rank_title = '';
            if ((int) $selected_user['user_rank'] !== 0) {
                $sql = 'SELECT rank_title
                        FROM ' . RANKS_TABLE . '
                        WHERE rank_id = ' . (int) $selected_user['user_rank'];
                $result             = $db->sql_query_limit($sql, 1);
                $current_rank_title = (string) $db->sql_fetchfield('rank_title');
                $db->sql_freeresult($result);
            }

            $template->assign_vars(array(
                'S_USER_SELECTED'    => true,
                'SELECTED_USER_ID'   => (int) $selected_user['user_id'],
                'SELECTED_USERNAME'  => $selected_user['username'],
                'CURRENT_RANK_ID'    => (int) $selected_user['user_rank'],
                'CURRENT_RANK_TITLE' => $current_rank_title !== '' ? $current_rank_title : $user->lang['TITLEFAIRY_NO_RANK'],
                'U_ACTION_ASSIGN'    => $this->u_action . '&amp;action=assign&amp;username=' . urlencode($selected_user['username']),
            ));
        }

        $template->assign_vars(array(
            'U_ACTION'       => $this->u_action,
            'S_SEARCH_VALUE' => $username,
        ));
    }
}
