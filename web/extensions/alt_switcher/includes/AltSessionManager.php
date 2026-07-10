<?php

namespace mafiascum\alt_switcher\includes;

class AltSessionManager
{
    const TABLE = 'alt_switcher_sessions';
    const COOKIE_NAME = 'altbrowser';
    const COOKIE_LIFETIME = 31536000;

    protected $db;
    protected $config;
    protected $user;
    protected $request;
    protected $table_prefix;

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\request\request_interface $request, $table_prefix)
    {
        $this->db = $db;
        $this->config = $config;
        $this->user = $user;
        $this->request = $request;
        $this->table_prefix = $table_prefix;
    }

    protected function table()
    {
        return $this->table_prefix . self::TABLE;
    }

    public function get_browser_id()
    {
        $raw = $this->request->variable($this->config['cookie_name'] . '_' . self::COOKIE_NAME, '', false, \phpbb\request\request_interface::COOKIE);
        return (strlen($raw) === 32 && ctype_xdigit($raw)) ? $raw : '';
    }

    public function ensure_browser_id()
    {
        $browser_id = $this->get_browser_id();
        if ($browser_id !== '') {
            return $browser_id;
        }
        $browser_id = bin2hex(random_bytes(16));
        $this->user->set_cookie(self::COOKIE_NAME, $browser_id, time() + self::COOKIE_LIFETIME);
        return $browser_id;
    }

    public function clear_browser_cookie()
    {
        $this->user->set_cookie(self::COOKIE_NAME, '', 1);
    }

    public function background_user($browser_id, $user_id)
    {
        $now = time();

        $this->db->sql_query('DELETE FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'
            AND user_id = ' . (int) $user_id);

        $alt_row = array(
            'browser_id'    => $browser_id,
            'user_id'       => (int) $user_id,
            'autologin_key' => '',
            'created_at'    => $now,
            'last_used_at'  => $now,
        );
        $this->db->sql_query('INSERT INTO ' . $this->table() . ' ' . $this->db->sql_build_array('INSERT', $alt_row));
    }

    public function get_alts($browser_id)
    {
        if ($browser_id === '') {
            return array();
        }
        $sql = 'SELECT user_id, autologin_key, last_used_at
            FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'
            ORDER BY last_used_at DESC';
        $result = $this->db->sql_query($sql);
        $rows = array();
        while ($row = $this->db->sql_fetchrow($result)) {
            $rows[] = $row;
        }
        $this->db->sql_freeresult($result);
        return $rows;
    }

    public function get_alt($browser_id, $user_id)
    {
        if ($browser_id === '') {
            return null;
        }
        $sql = 'SELECT user_id, autologin_key
            FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'
            AND user_id = ' . (int) $user_id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        return $row ?: null;
    }

    public function pop_most_recent($browser_id)
    {
        if ($browser_id === '') {
            return null;
        }
        $sql = 'SELECT user_id, autologin_key
            FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'
            ORDER BY last_used_at DESC';
        $result = $this->db->sql_query_limit($sql, 1);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        if (!$row) {
            return null;
        }
        $this->remove_alt($browser_id, (int) $row['user_id']);
        return $row;
    }

    public function remove_alt($browser_id, $user_id)
    {
        $sql = 'DELETE FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'
            AND user_id = ' . (int) $user_id;
        $this->db->sql_query($sql);
    }

    public function purge_autologin_key($user_id, $autologin_key)
    {
        $sql = 'DELETE FROM ' . SESSIONS_KEYS_TABLE . '
            WHERE user_id = ' . (int) $user_id . '
            AND key_id = \'' . $this->db->sql_escape(md5($autologin_key)) . '\'';
        $this->db->sql_query($sql);
    }

    public function wipe_browser($browser_id)
    {
        if ($browser_id === '') {
            return;
        }
        $alts = $this->get_alts($browser_id);
        foreach ($alts as $alt) {
            $this->purge_autologin_key((int) $alt['user_id'], (string) $alt['autologin_key']);
            $this->kill_sessions_for_user((int) $alt['user_id']);
        }
        $sql = 'DELETE FROM ' . $this->table() . '
            WHERE browser_id = \'' . $this->db->sql_escape($browser_id) . '\'';
        $this->db->sql_query($sql);
    }

    public function kill_sessions_for_user($user_id)
    {
        $sql = 'DELETE FROM ' . SESSIONS_TABLE . '
            WHERE session_user_id = ' . (int) $user_id;
        $this->db->sql_query($sql);
    }
}
?>
