<?php

namespace mafiascum\votecounter\acp;

class whitelist_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $template, $request, $db, $user;
        global $phpbb_root_path, $phpEx;

        $table_prefix    = 'phpbb_';
        $whitelist_table = $table_prefix . 'votecounter_topics';

        $this->tpl_name   = 'acp_vc_whitelist_body';
        $this->page_title = 'ACP_VC_WHITELIST';

        add_form_key('mafiascum_votecounter_whitelist');

        $action = $request->variable('action', '');

        if ($action === 'add' && $request->is_set_post('submit')) {
            if (!check_form_key('mafiascum_votecounter_whitelist')) {
                trigger_error('FORM_INVALID', E_USER_WARNING);
            }

            $topic_id = (int) $request->variable('topic_id', 0);
            if (!$topic_id) {
                trigger_error('You must provide a topic ID.' . adm_back_link($this->u_action), E_USER_WARNING);
            }

            $sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
            $result = $db->sql_query_limit($sql, 1);
            $topic_exists = (bool) $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if (!$topic_exists) {
                trigger_error('Topic not found.' . adm_back_link($this->u_action), E_USER_WARNING);
            }

            $sql = 'SELECT COUNT(*) AS cnt FROM ' . $whitelist_table . ' WHERE topic_id = ' . $topic_id;
            $result = $db->sql_query($sql);
            $already = (int) $db->sql_fetchfield('cnt') > 0;
            $db->sql_freeresult($result);

            if ($already) {
                trigger_error('Topic is already whitelisted.' . adm_back_link($this->u_action), E_USER_WARNING);
            }

            $sql = 'INSERT INTO ' . $whitelist_table . ' ' . $db->sql_build_array('INSERT', [
                'topic_id'         => $topic_id,
                'created_at'       => time(),
                'added_by_user_id' => (int) $user->data['user_id'],
            ]);
            $db->sql_query($sql);

            trigger_error('Topic added to whitelist.' . adm_back_link($this->u_action));
        }

        if ($action === 'remove') {
            $topic_id = (int) $request->variable('topic_id', 0);
            if ($topic_id) {
                $sql = 'DELETE FROM ' . $whitelist_table . ' WHERE topic_id = ' . $topic_id;
                $db->sql_query($sql);
                trigger_error('Topic removed from whitelist.' . adm_back_link($this->u_action));
            }
        }

        $sql = 'SELECT w.topic_id, w.created_at, w.added_by_user_id, t.topic_title, t.forum_id, u.username
                FROM ' . $whitelist_table . ' w
                LEFT JOIN ' . TOPICS_TABLE . ' t ON w.topic_id = t.topic_id
                LEFT JOIN ' . USERS_TABLE . ' u ON w.added_by_user_id = u.user_id
                ORDER BY w.created_at DESC, w.topic_id DESC';
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result)) {
            $template->assign_block_vars('whitelist', [
                'TOPIC_ID'    => (int) $row['topic_id'],
                'TOPIC_TITLE' => $row['topic_title'] !== null ? $row['topic_title'] : '(topic not found)',
                'CREATED_AT'  => !empty($row['created_at']) ? $user->format_date((int) $row['created_at']) : '',
                'ADDED_BY'    => $row['username'] !== null ? $row['username'] : '',
                'U_VIEW'      => append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . (int) $row['topic_id']),
                'U_REMOVE'    => $this->u_action . '&amp;action=remove&amp;topic_id=' . (int) $row['topic_id'],
            ]);
        }
        $db->sql_freeresult($result);

        $template->assign_vars([
            'U_ACTION' => $this->u_action,
        ]);
    }
}
