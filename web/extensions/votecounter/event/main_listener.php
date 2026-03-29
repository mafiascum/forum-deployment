<?php

namespace mafiascum\votecounter\event;

require_once(dirname(__FILE__) . "/../utils/bot.php");

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use mafiascum\votecounter\utils\BotPoster;

class main_listener implements EventSubscriberInterface
{
    /** @var \phpbb\template\template */
    protected $template;

    /** @var \phpbb\user */
    protected $user;

    /** @var \phpbb\db\driver\driver */
    protected $db;

    /** @var \phpbb\auth\auth */
    protected $auth;

    protected $config;

    protected $request;

    protected $user_loader;

    protected $helper;

    public function __construct(\phpbb\template\template $template, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, $table_prefix, \phpbb\auth\auth $auth, \phpbb\request\request $request, \phpbb\user_loader $user_loader, \phpbb\config\config $config, \phpbb\controller\helper $helper)
    {
        $this->template = $template;
        $this->user = $user;
        $this->db = $db;
        $this->table_prefix = $table_prefix;
        $this->auth = $auth;
        $this->request = $request;
        $this->user_loader = $user_loader;
        $this->config = $config;
        $this->helper = $helper;
    }


    static public function getSubscribedEvents()
    {
        return array(
            'core.posting_modify_template_vars' => 'add_votecounter_panel',
            'core.posting_modify_submission_errors' => 'validate_vote',
            'core.submit_post_end' => 'submit_post_end',
            'core.viewtopic_assign_template_vars_before' => 'inject_template_vars',
        );
    }

    public function add_votecounter_panel($event)
    {
        $this->user->add_lang_ext('mafiascum/votecounter', 'votecounter');

        $event_data = $event->get_data();
        $forum_id = $event_data['forum_id'];
        $topic_id = $event_data['topic_id'];

        if (!$forum_id) {
            $this->template->assign_vars([
                'S_VOTECOUNTER_PANEL' => false,
            ]);
            return;
        }

        $is_whitelisted = false;
        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        $is_whitelisted = $count > 0;

        if (!$is_whitelisted) {
            $this->template->assign_vars([
                'S_VOTECOUNTER_PANEL' => false,
            ]);
            return;
        }

        $has_auth = $this->auth->acl_get('m_edit', $forum_id);
        if (!$has_auth) {
            $user_id = (int) $this->user->data['user_id'];
            $sql = 'SELECT 1 FROM ' . $this->table_prefix . 'topic_mod
                    WHERE topic_id = ' . $topic_id . '
                    AND user_id = ' . $user_id;
            $result = $this->db->sql_query_limit($sql, 1);
            $has_auth = (bool) $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);
        }
        $this->template->assign_vars([
            'S_VOTECOUNTER_PANEL' => $has_auth,
            'VC_TOPIC_ID' => $topic_id,
        ]);
    }

    public function validate_vote($event)
    {
        $topic_id = (int) $event['topic_id'];
        $post_data = $event['post_data'];

        if (!$topic_id) {
            return;
        }

        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        if ($count === 0) {
            return;
        }

        $sql = 'SELECT id FROM ' . $this->table_prefix . 'games WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $game = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        if (!$game) {
            return;
        }

        $message = $this->request->variable('message', '', true);
        if (!preg_match_all('/\[(?:vote|v)\](.*?)\[\/(?:vote|v)\]/i', $message, $matches)) {
            return;
        }

        $sql = 'SELECT u.username, u.username_clean
                FROM ' . $this->table_prefix . 'players p
                JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                WHERE p.game_id = ' . (int) $game['id'] . '
                AND p.died_at IS NULL';
        $result = $this->db->sql_query($sql);
        $alive_players = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $alive_players[$row['username_clean']] = $row['username'];
        }
        $this->db->sql_freeresult($result);

        foreach ($matches[1] as $target) {
            $target_clean = utf8_clean_string(trim($target));
            if (!isset($alive_players[$target_clean])) {
                $valid_list = implode(', ', array_values($alive_players));
                $error = $event['error'];
                $error[] = 'Invalid vote target "' . htmlspecialchars(trim($target), ENT_QUOTES) . '". Valid alive players: ' . htmlspecialchars($valid_list, ENT_QUOTES);
                $event['error'] = $error;
                return;
            }
        }
    }

    public function submit_post_end($event)
    {
        $raw = $event->get_data();
        $data = $raw['data'] ?? [];

        if (!$data) {
            return;
        }

        $topic_id   = (int) ($data['topic_id'] ?? 0);
        $forum_id   = (int) ($data['forum_id'] ?? 0);
        $post_id    = (int) ($data['post_id'] ?? 0);
        $poster_id  = (int) ($data['poster_id'] ?? 0);
        $topic_title = (string) ($data['topic_title'] ?? '');

        if (!$topic_id || !$forum_id || !$post_id || !$poster_id) {
            return;
        }

        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);
        if ($count === 0) {
            return;
        }

        $sql = 'SELECT id FROM ' . $this->table_prefix . 'games WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $game = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        if (!$game) {
            return;
        }

        $sql = 'SELECT COUNT(post_id) AS topic_post_number
                FROM ' . $this->table_prefix . 'posts
                WHERE topic_id = ' . $topic_id . '
                AND post_id <= ' . $post_id;
        $result = $this->db->sql_query($sql);
        if (!$result) {
            return;
        }
        $post_number = (int) $this->db->sql_fetchfield('topic_post_number');
        $this->db->sql_freeresult($result);

        $game_id = (int) $game['id'];
        $message = $this->request->variable('message', '', true);

        $sql = 'SELECT id FROM ' . $this->table_prefix . 'players
                WHERE game_id = ' . $game_id . '
                AND user_id = ' . $poster_id;
        $result = $this->db->sql_query_limit($sql, 1);
        $voter = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);

        if ($voter) {
            $voter_id = (int) $voter['id'];

            if (preg_match('/\[(?:unvote|uv)\]/i', $message)) {
                $sql = 'INSERT INTO ' . $this->table_prefix . 'game_votes
                        (game_id, voter_player_id, target_player_id, post_number)
                        VALUES (' . $game_id . ', ' . $voter_id . ', NULL, ' . $post_number . ')';
                $this->db->sql_query($sql);
            }

            if (preg_match_all('/\[(?:vote|v)\](.*?)\[\/(?:vote|v)\]/i', $message, $matches)) {
                $sql = 'SELECT p.id, u.username_clean
                        FROM ' . $this->table_prefix . 'players p
                        JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                        WHERE p.game_id = ' . $game_id;
                $result = $this->db->sql_query($sql);
                $player_by_clean = [];
                while ($row = $this->db->sql_fetchrow($result)) {
                    $player_by_clean[$row['username_clean']] = (int) $row['id'];
                }
                $this->db->sql_freeresult($result);

                foreach ($matches[1] as $target) {
                    $target_clean = utf8_clean_string(trim($target));
                    if (isset($player_by_clean[$target_clean])) {
                        $target_player_id = $player_by_clean[$target_clean];
                        $sql = 'INSERT INTO ' . $this->table_prefix . 'game_votes
                                (game_id, voter_player_id, target_player_id, post_number)
                                VALUES (' . $game_id . ', ' . $voter_id . ', ' . $target_player_id . ', ' . $post_number . ')';
                        $this->db->sql_query($sql);
                    }
                }
            }
        }

        $posts_per_page = max(1, (int) ($this->config['posts_per_page'] ?? 25));
        if ($post_number % $posts_per_page === 0) {
            $bot_user_id = 35786;
            $vc_message = $this->buildVoteCountMessage($game_id, $post_number);
            BotPoster::postMessage(
                $bot_user_id,
                $forum_id,
                $topic_id,
                $vc_message,
                $topic_title,
                $this->user,
                $this->user_loader
            );
        }
    }

    private function buildVoteCountMessage(int $game_id, int $post_number): string
    {
        $sql = 'SELECT * FROM ' . $this->table_prefix . 'game_days
                WHERE game_id = ' . $game_id . '
                ORDER BY day_number DESC';
        $result = $this->db->sql_query($sql);
        $days = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $days[] = $row;
        }
        $this->db->sql_freeresult($result);

        $day = null;
        foreach ($days as $d) {
            if ($d['end_post_number'] === null || $d['end_post_number'] === '') {
                $day = $d;
                break;
            }
        }
        if (!$day && !empty($days)) {
            $day = $days[0];
        }

        $start_post = $day ? (int) $day['start_post_number'] : 1;
        $end_post   = ($day && $day['end_post_number']) ? (int) $day['end_post_number'] : null;

        $sql = 'SELECT p.id, u.username
                FROM ' . $this->table_prefix . 'players p
                JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                WHERE p.game_id = ' . $game_id . '
                AND p.died_at IS NULL
                ORDER BY u.username ASC';
        $result = $this->db->sql_query($sql);
        $players = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $players[(int) $row['id']] = $row['username'];
        }
        $this->db->sql_freeresult($result);

        $alive_count = count($players);
        $majority    = (int) floor($alive_count / 2) + 1;

        $end_cond = $end_post !== null ? ' AND gv.post_number <= ' . $end_post : '';
        $sql = 'SELECT gv.voter_player_id, gv.target_player_id, gv.post_number, gv.post_id,
                       tu.username AS target_name
                FROM ' . $this->table_prefix . 'game_votes gv
                LEFT JOIN ' . $this->table_prefix . 'players tp ON gv.target_player_id = tp.id
                LEFT JOIN ' . USERS_TABLE . ' tu ON tp.user_id = tu.user_id
                WHERE gv.game_id = ' . $game_id . '
                AND gv.post_number >= ' . $start_post .
            $end_cond . '
                ORDER BY gv.post_number ASC';
        $result = $this->db->sql_query($sql);
        $vote_rows = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $vote_rows[] = $row;
        }
        $this->db->sql_freeresult($result);

        $current_votes = [];
        foreach ($vote_rows as $row) {
            $voter_id = (int) $row['voter_player_id'];

            if ($row['target_player_id'] === null) {
                $current_votes[$voter_id] = null;
            } else {
                $current_votes[$voter_id] = [
                    'target_id'   => (int) $row['target_player_id'],
                    'target_name' => $row['target_name'],
                    'post_number' => (int) $row['post_number'],
                    'post_id'     => (int) $row['post_id'],
                ];
            }

            $tally = [];
            foreach ($players as $pid => $_) {
                $v = $current_votes[$pid] ?? null;
                if ($v !== null) {
                    $tally[$v['target_id']] = ($tally[$v['target_id']] ?? 0) + 1;
                }
            }
            foreach ($tally as $count) {
                if ($count >= $majority) {
                    break 2;
                }
            }
        }

        $votes_by_target = [];
        $not_voting      = [];

        foreach ($players as $pid => $pname) {
            $v = $current_votes[$pid] ?? null;
            if ($v === null) {
                $not_voting[] = $pname;
            } else {
                $tid = $v['target_id'];
                if (!isset($votes_by_target[$tid])) {
                    $votes_by_target[$tid] = ['name' => $v['target_name'], 'voters' => []];
                }
                $votes_by_target[$tid]['voters'][] = [
                    'name'        => $pname,
                    'post_number' => $v['post_number'],
                    'post_id'     => $v['post_id'],
                ];
            }
        }

        uasort($votes_by_target, function ($a, $b) {
            return count($b['voters']) - count($a['voters']);
        });

        $lines = [];
        foreach ($votes_by_target as $entry) {
            $count        = count($entry['voters']);
            $voter_parts  = [];
            foreach ($entry['voters'] as $v) {
                $voter_parts[] = $v['name'] . ' ([post]' . $v['post_number'] . '[/post])';
            }
            $lines[] = '[b]' . $entry['name'] . ' (' . $count . '/' . $alive_count . ')[/b] -> ' . implode(', ', $voter_parts);
        }

        if (!empty($not_voting)) {
            $lines[] = '';
            $lines[] = '[b]Not Voting (' . count($not_voting) . ')[/b] -> ' . implode(', ', $not_voting);
        }

        return '[area=Current Votes]' . implode("\n", $lines) . '[/area]';
    }

    public function inject_template_vars($event)
    {
        $topic_id = (int) $event['topic_id'];
        $forum_id = (int) $event['forum_id'];
        $user_id  = (int) $this->user->data['user_id'];

        $has_permission = $this->auth->acl_get('m_edit', $forum_id);
        if (!$has_permission) {
            $sql = 'SELECT 1 FROM ' . $this->table_prefix . 'topic_mod
                    WHERE topic_id = ' . $topic_id . '
                    AND user_id = ' . $user_id;
            $result = $this->db->sql_query_limit($sql, 1);
            $has_permission = (bool) $this->db->sql_fetchrow($result);
            $this->db->sql_freeresult($result);
        }

        if (!$has_permission) {
            return;
        }

        $sql = 'SELECT COUNT(*) AS cnt FROM ' . $this->table_prefix . 'votecounter_topics WHERE topic_id = ' . $topic_id;
        $result = $this->db->sql_query($sql);
        $count = (int) $this->db->sql_fetchfield('cnt');
        $this->db->sql_freeresult($result);

        if ($count === 0) {
            return;
        }

        $this->template->assign_vars([
            'U_MANAGE_GAME' => $this->helper->route('game_manager_router', ['topic_id' => $topic_id])
        ]);
    }
}
