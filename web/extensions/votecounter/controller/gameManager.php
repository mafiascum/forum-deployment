<?php

namespace mafiascum\votecounter\controller;

use Symfony\Component\HttpFoundation\Response;
use mafiascum\votecounter\utils\VoteCounter;

class gameManager
{
    protected $helper;
    protected $language;
    protected $template;
    protected $db;
    protected $user;
    protected $auth;
    protected $request;

    public function __construct(
        \phpbb\controller\helper $helper,
        \phpbb\language\language $language,
        \phpbb\template\template $template,
        \phpbb\db\driver\driver_interface $db,
        \phpbb\user $user,
        \phpbb\auth\auth $auth,
        \phpbb\request\request $request
    ) {
        $this->helper   = $helper;
        $this->language = $language;
        $this->template = $template;
        $this->db       = $db;
        $this->user     = $user;
        $this->auth     = $auth;
        $this->request  = $request;

        $this->language->add_lang('votecounter', 'mafiascum/votecounter');
    }

    public function handle($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $is_whitelisted = false;
        $sql = 'SELECT COUNT(*) AS cnt 
            FROM ' . $table_prefix . 'votecounter_topics
            WHERE topic_id = ' . (int) $topic_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            trigger_error('UNEXPECTED_ERROR_001');
        }
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        $is_whitelisted = $count > 0;

        if (!$is_whitelisted) {
            trigger_error('NOT_WHITELISTED');
        }

        $game = $this->fetchGame($topic_id);
        $votecounter_exists = ($game !== null);

        $active_tab = $this->request->variable('tab', 'players');

        if ($votecounter_exists) {
            $this->assignPlayerTabVars($topic_id, $game);
        }

        $this->template->assign_vars([
            'TOPIC_ID' => $topic_id,
            'ACTIVE_TAB' => $active_tab,
            'VOTECOUNTER_EXISTS' => $votecounter_exists,
            'U_CREATE_VOTECOUNTER' => $this->helper->route('game_create', [
                'topic_id' => $topic_id
            ]),
            'U_TAB_PLAYERS' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'players']),
            'U_TAB_DAYS'    => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'days']),
            'U_TAB_SETTINGS' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'settings']),
            'U_TAB_VOTECOUNT' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'votecount']),
        ]);

        return $this->helper->render('@mafiascum_votecounter/manage_game.html', $this->language->lang('MANAGE_GAME'));
    }

    public function tab($topic_id, $tab)
    {
        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        switch ($tab) {
            case 'players':
                return $this->renderPlayerTab($topic_id, $game);
            case 'days':
                return $this->renderDayTab($topic_id, $game);
            case 'settings':
                return $this->renderSettingsTab();
            case 'votecount':
                return $this->renderVotecountTab($topic_id, $game);
        }

        return new Response('', 200);
    }

    public function create_votecounter($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $table_prefix . 'votecounter_topics WHERE topic_id = ' . (int) $topic_id;
        $result = $this->db->sql_query($sql);
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        if ($count === 0) {
            trigger_error('NOT_WHITELISTED');
        }

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            $sql = 'INSERT INTO ' . $table_prefix . 'games ' . $this->db->sql_build_array('INSERT', [
                'topic_id' => (int) $topic_id,
                'created_at' => time(),
            ]);
            $this->db->sql_query($sql);
            $game = $this->fetchGame($topic_id);
        }

        return $this->renderPlayerTab($topic_id, $game);
    }

    public function create_player($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');

        if (!$username) {
            trigger_error('USERNAME_CANT_BE_EMPTY');
        }

        $sql = 'SELECT user_id FROM ' . USERS_TABLE . '
                WHERE username_clean = \'' . $this->db->sql_escape(utf8_clean_string($username)) . '\'';
        $result = $this->db->sql_query($sql);
        $user_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        $user_id = $user_row ? (int) $user_row['user_id'] : 0;
        if (!$user_id) {
            trigger_error('USER_NOT_FOUND');
        }

        $sql = 'INSERT INTO ' . $table_prefix . 'players ' . $this->db->sql_build_array(
            'INSERT',
            [
                'game_id' => (int) $game['id'],
                'user_id' => (int) $user_id,
                'created_at' => time()
            ]
        );
        $this->db->sql_query($sql);

        return $this->renderPlayerTab($topic_id, $game);
    }

    public function edit_player($topic_id, $player_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $sql = 'SELECT p.*, u.username
                FROM ' . $table_prefix . 'players p
                JOIN ' . $table_prefix . 'games g ON p.game_id = g.id
                JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                WHERE p.id = ' . (int) $player_id . '
                AND g.topic_id = ' . (int) $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $player = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$player) {
            trigger_error('PLAYER_NOT_FOUND');
        }

        $this->template->assign_vars([
            'USERNAME'  => $player['username'],
            'DIED_AT'   => $player['died_at'],
            'U_UPDATE'  => $this->helper->route('player_update', ['topic_id' => $topic_id, 'player_id' => $player_id]),
            'U_DELETE'  => $this->helper->route('player_delete', ['topic_id' => $topic_id, 'player_id' => $player_id]),
            'U_PLAYERS' => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'players']),
        ]);

        $this->template->set_filenames(['individual_player' => '@mafiascum_votecounter/forms/individual_player.html']);
        $content = $this->template->assign_display('individual_player', '', true);
        return new Response($content, 200);
    }

    public function update_player($topic_id, $player_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $died_at = (isset($data['died_at']) && $data['died_at'] !== '') ? (int) $data['died_at'] : null;

        if (!$username) {
            trigger_error('USERNAME_CANT_BE_EMPTY');
        }

        $sql = 'SELECT user_id FROM ' . USERS_TABLE . '
                WHERE username_clean = \'' . $this->db->sql_escape(utf8_clean_string($username)) . '\'';
        $result = $this->db->sql_query($sql);
        $user_row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        $user_id = $user_row ? (int) $user_row['user_id'] : 0;

        if (!$user_id) {
            trigger_error('USER_NOT_FOUND');
        }

        $died_at_sql = ($died_at !== null) ? (int) $died_at : 'NULL';
        $sql = 'UPDATE ' . $table_prefix . 'players
                SET user_id = ' . $user_id . ', died_at = ' . $died_at_sql . '
                WHERE id = ' . (int) $player_id . '
                AND game_id = ' . (int) $game['id'];
        $this->db->sql_query($sql);

        return $this->renderPlayerTab($topic_id, $game);
    }

    public function delete_player($topic_id, $player_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $this->db->sql_query('DELETE FROM ' . $table_prefix . 'players WHERE id = ' . (int) $player_id . ' AND game_id = ' . (int) $game['id']);

        return $this->renderPlayerTab($topic_id, $game);
    }

    public function create_day($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $day_number = isset($data['day_number']) && $data['day_number'] !== '' ? (int) $data['day_number'] : 0;
        $start_post = isset($data['start_post_number']) && $data['start_post_number'] !== '' ? (int) $data['start_post_number'] : 0;
        $end_post = isset($data['end_post_number']) && $data['end_post_number'] !== '' ? (int) $data['end_post_number'] : null;

        if (!$day_number || !$start_post) {
            trigger_error('DAY_FIELDS_REQUIRED');
        }

        $sql = 'INSERT INTO ' . $table_prefix . 'game_days ' . $this->db->sql_build_array('INSERT', [
            'game_id'           => (int) $game['id'],
            'day_number'        => $day_number,
            'start_post_number' => $start_post,
        ]);
        $this->db->sql_query($sql);

        if ($end_post !== null) {
            $day_id = $this->db->sql_nextid();
            $this->db->sql_query('UPDATE ' . $table_prefix . 'game_days SET end_post_number = ' . $end_post . ' WHERE id = ' . $day_id);
        }

        return $this->renderDayTab($topic_id, $game);
    }

    public function edit_day($topic_id, $day_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $sql = 'SELECT gd.*
                FROM ' . $table_prefix . 'game_days gd
                JOIN ' . $table_prefix . 'games g ON gd.game_id = g.id
                WHERE gd.id = ' . (int) $day_id . '
                AND g.topic_id = ' . (int) $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $day = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$day) {
            trigger_error('DAY_NOT_FOUND');
        }

        $this->template->assign_vars([
            'DAY_NUMBER'        => $day['day_number'],
            'START_POST_NUMBER' => $day['start_post_number'],
            'END_POST_NUMBER'   => $day['end_post_number'],
            'U_UPDATE'          => $this->helper->route('day_update', ['topic_id' => $topic_id, 'day_id' => $day_id]),
            'U_DELETE'          => $this->helper->route('day_delete', ['topic_id' => $topic_id, 'day_id' => $day_id]),
            'U_DAYS'            => $this->helper->route('game_manage_tab', ['topic_id' => $topic_id, 'tab' => 'days']),
        ]);

        $this->template->set_filenames(['individual_day' => '@mafiascum_votecounter/forms/individual_day.html']);
        $content = $this->template->assign_display('individual_day', '', true);
        return new Response($content, 200);
    }

    public function update_day($topic_id, $day_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $day_number = isset($data['day_number']) && $data['day_number'] !== '' ? (int) $data['day_number'] : 0;
        $start_post = isset($data['start_post_number']) && $data['start_post_number'] !== '' ? (int) $data['start_post_number'] : 0;
        $end_post = isset($data['end_post_number']) && $data['end_post_number'] !== '' ? (int) $data['end_post_number'] : null;

        if (!$day_number || !$start_post) {
            trigger_error('DAY_FIELDS_REQUIRED');
        }

        $end_post_sql = ($end_post !== null) ? $end_post : 'NULL';
        $sql = 'UPDATE ' . $table_prefix . 'game_days
                SET day_number = ' . $day_number . ',
                    start_post_number = ' . $start_post . ',
                    end_post_number = ' . $end_post_sql . '
                WHERE id = ' . (int) $day_id . '
                AND game_id = ' . (int) $game['id'];
        $this->db->sql_query($sql);

        return $this->renderDayTab($topic_id, $game);
    }

    public function delete_day($topic_id, $day_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $this->db->sql_query('DELETE FROM ' . $table_prefix . 'game_days WHERE id = ' . (int) $day_id . ' AND game_id = ' . (int) $game['id']);

        return $this->renderDayTab($topic_id, $game);
    }

    public function votecount($thread_id)
    {
        $as_at = $this->request->is_set('as_at') ? (int) $this->request->variable('as_at', 0) : null;

        $game = $this->fetchGame($thread_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $result = VoteCounter::calculateVoteCount((int) $game['id'], $as_at);

        return new Response(json_encode($result), 200, ['Content-Type' => 'application/json']);
    }

    public function generate_votecount($topic_id)
    {
        global $table_prefix;

        if (!$topic_id) {
            trigger_error('NO_TOPIC');
        }

        $this->requirePermission($topic_id);

        $game = $this->fetchGame($topic_id);
        if (!$game) {
            trigger_error('GAME_NOT_FOUND');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $post_number = isset($data['post_number']) && $data['post_number'] !== '' ? (int) $data['post_number'] : null;

        $data = VoteCounter::calculateVoteCount((int) $game['id'], $post_number);
        $formatted = VoteCounter::formatVoteCount($data);

        return new Response(json_encode(['votecount' => $formatted]), 200, ['Content-Type' => 'application/json']);
    }

    // PARTIALS
    private function renderVotecountTab($topic_id, $game)
    {
        $this->template->assign_vars([
            'U_GENERATE_VOTECOUNT' => $this->helper->route('votecount_generate', ['topic_id' => $topic_id]),
        ]);

        $this->template->set_filenames(['votecount_tab' => '@mafiascum_votecounter/forms/votecount.html']);
        $content = $this->template->assign_display('votecount_tab', '', true);
        return new Response($content, 200);
    }

    private function renderSettingsTab()
    {
        $this->template->set_filenames(['settings_tab' => '@mafiascum_votecounter/forms/misc.html']);
        $content = $this->template->assign_display('settings_tab', '', true);
        return new Response($content, 200);
    }

    private function renderDayTab($topic_id, $game)
    {
        $this->assignDayTabVars($topic_id, $game);

        $this->template->set_filenames(['days_tab' => '@mafiascum_votecounter/forms/days.html']);
        $content = $this->template->assign_display('days_tab', '', true);
        return new Response($content, 200);
    }

    private function assignDayTabVars($topic_id, $game)
    {
        global $table_prefix;

        $sql = 'SELECT * FROM ' . $table_prefix . 'game_days
                WHERE game_id = ' . (int) $game['id'] . '
                ORDER BY day_number ASC';
        $result = $this->db->sql_query($sql);

        $this->template->destroy_block_vars('days');

        $days = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $this->template->assign_block_vars('days', [
                'DAY_NUMBER'        => $row['day_number'],
                'START_POST_NUMBER' => $row['start_post_number'],
                'END_POST_NUMBER'   => $row['end_post_number'],
                'U_EDIT'            => $this->helper->route('day_edit', ['topic_id' => $topic_id, 'day_id' => $row['id']]),
            ]);
            $days[] = $row;
        }
        $this->db->sql_freeresult($result);

        $ongoing_count = 0;
        foreach ($days as $day) {
            if ($day['end_post_number'] === null || $day['end_post_number'] === '') {
                $ongoing_count++;
            }
        }

        $has_overlapping = false;
        $n = count($days);
        for ($i = 0; $i < $n && !$has_overlapping; $i++) {
            $start_i = (int) $days[$i]['start_post_number'];
            $end_i   = ($days[$i]['end_post_number'] !== null && $days[$i]['end_post_number'] !== '') ? (int) $days[$i]['end_post_number'] : PHP_INT_MAX;
            for ($j = $i + 1; $j < $n && !$has_overlapping; $j++) {
                $start_j = (int) $days[$j]['start_post_number'];
                $end_j   = ($days[$j]['end_post_number'] !== null && $days[$j]['end_post_number'] !== '') ? (int) $days[$j]['end_post_number'] : PHP_INT_MAX;
                if ($start_i <= $end_j && $start_j <= $end_i) {
                    $has_overlapping = true;
                }
            }
        }

        $this->template->assign_vars([
            'U_ADD_DAY'             => $this->helper->route('day_create', ['topic_id' => $topic_id]),
            'WARN_NO_CURRENT_DAY'   => $n > 0 && $ongoing_count === 0,
            'WARN_MULTIPLE_ONGOING' => $ongoing_count > 1,
            'WARN_OVERLAPPING_DAYS' => $has_overlapping,
        ]);
    }

    private function renderPlayerTab($topic_id, $game)
    {
        $this->assignPlayerTabVars($topic_id, $game);

        $this->template->set_filenames(['players_tab' => '@mafiascum_votecounter/forms/players.html']);
        $content = $this->template->assign_display('players_tab', '', true);
        return new Response($content, 200);
    }

    private function assignPlayerTabVars($topic_id, $game)
    {
        global $table_prefix;

        $sql = 'SELECT p.*, u.username
                    FROM ' . $table_prefix . 'players p
                    JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                    WHERE p.game_id = ' .  (int) $game['id'];
        $result = $this->db->sql_query($sql);
        
        $this->template->destroy_block_vars('players');

        while ($row = $this->db->sql_fetchrow($result)) {
            $this->template->assign_block_vars('players', [
                'USERNAME' => $row['username'],
                'DIED_AT'  => $row['died_at'],
                'U_EDIT'   => $this->helper->route('player_edit', ['topic_id' => $topic_id, 'player_id' => $row['id']]),
            ]);
        }
        $this->db->sql_freeresult($result);

        $this->template->assign_vars([
            'U_ADD_PLAYER' => $this->helper->route('player_create', ['topic_id' => $topic_id]),
        ]);
    }

    private function requirePermission(int $topic_id): void
    {
        global $table_prefix;

        $sql = 'SELECT forum_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        $forum_id = $row ? (int) $row['forum_id'] : 0;

        if ($forum_id && $this->auth->acl_get('m_edit', $forum_id)) {
            return;
        }

        $user_id = (int) $this->user->data['user_id'];
        $sql = 'SELECT 1 FROM ' . $table_prefix . 'topic_mod
                WHERE topic_id = ' . $topic_id . '
                AND user_id = ' . $user_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $is_topic_mod = (bool) $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($is_topic_mod) {
            return;
        }

        $sql = 'SELECT 1 FROM ' . TOPICS_TABLE . '
                WHERE topic_id = ' . $topic_id . '
                AND topic_poster = ' . $user_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $is_topic_poster = (bool) $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if (!$is_topic_poster) {
            trigger_error('NO_PERMISSION');
        }
    }

    private function fetchGame($topic_id)
    {
        global $table_prefix;

        $sql = 'SELECT *
            FROM ' . $table_prefix . 'games
            WHERE topic_id = ' . (int) $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $game = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        return $game ?: null;
    }
}
