<?php

namespace mafiascum\votecounter\event;

require_once(dirname(__FILE__) . "/../utils/bot.php");
require_once(dirname(__FILE__) . "/../utils/VoteCounter.php");

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use mafiascum\votecounter\utils\BotPoster;
use mafiascum\votecounter\utils\VoteCounter;

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
            'core.posting_modify_submission_errors' => 'validate_vote',
            'core.submit_post_end' => 'submit_post_end',
            'core.viewtopic_assign_template_vars_before' => 'inject_template_vars',
        );
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

        $message = $this->stripIgnoredSections($this->request->variable('message', '', true));
        if (!preg_match_all('/\[(?:vote|v)\](.*?)(?:\[\/(?:vote|v)\]|$)/im', $message, $matches)) {
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

        $bot_user_id = 35786;
        if (!$topic_id || !$forum_id || !$post_id || !$poster_id || $poster_id === $bot_user_id) {
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
        $post_number = (int) $this->db->sql_fetchfield('topic_post_number') - 1;
        $this->db->sql_freeresult($result);

        $game_id = (int) $game['id'];
        $message = $this->stripIgnoredSections($this->request->variable('message', '', true));

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

            if (preg_match_all('/\[(?:vote|v)\](.*?)(?:\[\/(?:vote|v)\]|$)/im', $message, $matches)) {
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

        $is_local = (getenv('MAFIASCUM_ENVIRONMENT') === 'local');
        $posts_per_page = max(1, (int) ($this->config['posts_per_page'] ?? 25));
        if ($is_local || ($post_number + 1) % $posts_per_page === 0) {
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

    private function stripIgnoredSections(string $message): string
    {
        $prev = null;
        while ($message !== $prev) {
            $prev = $message;
            $message = preg_replace('/\[quote[^\]]*\](?:(?!\[quote)[\s\S])*?\[\/quote\]/is', '', $message);
            $message = preg_replace('/\[spoiler=[^\]]*\](?:(?!\[spoiler)[\s\S])*?\[\/spoiler\]/is', '', $message);
        }
        return $message;
    }

    private function buildVoteCountMessage(int $game_id, int $post_number): string
    {
        $data = VoteCounter::calculateVoteCount($game_id, $post_number);
        return VoteCounter::formatVoteCount($data);
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
            $sql = 'SELECT 1 FROM ' . TOPICS_TABLE . '
                    WHERE topic_id = ' . $topic_id . '
                    AND topic_poster = ' . $user_id;
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
