<?php

namespace mafiascum\votecounter\utils;

class VoteCounter
{

    public static function fetchVoteCountData(int $game_id, ?int $as_at): array
    {

        global $phpbb_container;
        $db = $phpbb_container->get('dbal.conn');
        $table_prefix = $phpbb_container->getParameter('core.table_prefix');

        // Find the appropriate day
        if ($as_at) {
            $sql = 'SELECT *
                    FROM ' . $table_prefix . 'game_days
                    WHERE game_id = ' . $game_id . '
                        AND start_post_number <= ' . (int) $as_at . '
                    ORDER BY start_post_number DESC';
            $result = $db->sql_query_limit($sql, 1);
        } else {
            $sql = 'SELECT *
                    FROM ' . $table_prefix . 'game_days
                    WHERE game_id = ' . $game_id . '
                    ORDER BY start_post_number DESC';
            $result = $db->sql_query_limit($sql, 1);
        }

        $day = $db->sql_fetchrow($result);
        $db->sql_freeresult($result);

        if (!$day) {
            return array("day" => null, "players" => [], "votes" => []);
        }

        $cutoff = $as_at ?? $day['end_post_number'];

        // Fetch all living players
        $died_at_condition = $cutoff !== null
            ? '(p.died_at IS NULL OR p.died_at > ' . (int) $cutoff . ')'
            : 'p.died_at IS NULL';

        $sql = 'SELECT p.id, u.username, p.died_at
                FROM ' . $table_prefix . 'players p
                JOIN ' . USERS_TABLE . ' u ON p.user_id = u.user_id
                WHERE p.game_id = ' . $game_id . '
                    AND ' . $died_at_condition . '
                ORDER BY u.username ASC';
        $result = $db->sql_query($sql);
        $players = [];
        while ($row = $db->sql_fetchrow($result)) {
            $players[] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'died_at'  => $row['died_at'],
            ];
        }
        $db->sql_freeresult($result);

        // Fetch all votes registered under game_id
        $vote_start = (int) $day['start_post_number'];
        $upper_bound_condition = $cutoff !== null ? ' AND post_number <= ' . (int) $cutoff : '';

        $sql = 'SELECT *
                FROM ' . $table_prefix . 'game_votes
                WHERE game_id = ' . (int) $game_id . '
                    AND post_number >= ' . $vote_start . $upper_bound_condition . '
                ORDER BY post_number ASC';

        $result = $db->sql_query($sql);
        $votes = [];
        while ($row = $db->sql_fetchrow($result)) {
            $votes[] = array(
                'id' => $row['id'],
                'game_id' => $row['game_id'],
                'voter_player_id' => $row['voter_player_id'],
                'target_player_id' => $row['target_player_id'],
                'post_number' => $row['post_number']
            );
        }
        $db->sql_freeresult($result);

        return array(
            "day" => $day,
            "players" => $players,
            "votes" => $votes
        );
    }

    public static function calculateVoteCount(int $game_id, ?int $as_at): array
    {
        $data = self::fetchVoteCountData($game_id, $as_at);
        $day = $data['day'];
        $players = $data['players'];
        $votes = $data['votes'];

        $majority = (int) floor(count($players) / 2) + 1;

        $players_by_id = [];
        foreach ($players as $player) {
            $players_by_id[(int) $player['id']] = $player;
        }

        $current_votes = [];
        $wagons = [];

        foreach ($votes as $vote) {
            $voter_id   = (int) $vote['voter_player_id'];
            $target_id  = $vote['target_player_id'] !== null ? (int) $vote['target_player_id'] : null;
            $post_number = (int) $vote['post_number'];
            $voter_name = $players_by_id[$voter_id]['username'] ?? 'Unknown';

            $prev_target_id = $current_votes[$voter_id] ?? null;
            if ($prev_target_id !== null) {
                $prev_target_name = $players_by_id[$prev_target_id]['username'] ?? 'Unknown';
                unset($wagons[$prev_target_name][$voter_id]);
                if (empty($wagons[$prev_target_name])) {
                    unset($wagons[$prev_target_name]);
                }
            }

            $current_votes[$voter_id] = $target_id;

            if ($target_id !== null) {
                $target_name = $players_by_id[$target_id]['username'] ?? 'Unknown';
                $wagons[$target_name][$voter_id] = ['username' => $voter_name, 'post_number' => $post_number];

                if (count($wagons[$target_name]) >= $majority) {
                    break;
                }
            }
        }

        foreach ($wagons as $target_name => $voters) {
            $wagons[$target_name] = array_values($voters);
        }

        $not_voting = [];
        foreach ($players as $player) {
            $pid = (int) $player['id'];
            if (!isset($current_votes[$pid])) {
                $not_voting[] = ['username' => $player['username'], 'post_number' => null];
            } elseif ($current_votes[$pid] === null) {
                $unvote_post = null;
                foreach (array_reverse($votes) as $vote) {
                    if ((int) $vote['voter_player_id'] === $pid && $vote['target_player_id'] === null) {
                        $unvote_post = (int) $vote['post_number'];
                        break;
                    }
                }
                $not_voting[] = ['username' => $player['username'], 'post_number' => $unvote_post];
            }
        }

        return [
            'day' => $day,
            'majority' => $majority,
            'wagons' => $wagons,
            'not_voting' => $not_voting,
        ];
    }

    public static function formatVoteCount(array $data): string
    {
        $majority   = $data['majority'];
        $wagons     = $data['wagons'];
        $not_voting = $data['not_voting'];

        uasort($wagons, fn($a, $b) => count($b) - count($a));

        $lines = [];
        foreach ($wagons as $target_name => $voters) {
            $count        = count($voters);
            $voter_parts  = [];
            foreach ($voters as $v) {
                $voter_parts[] = $v['username'] . ' ([post]' . $v['post_number'] . '[/post])';
            }
            $lines[] = '[b]' . $target_name . ' (' . $count . '/' . $majority . ')[/b] -> ' . implode(', ', $voter_parts);
        }

        if (!empty($not_voting)) {
            $nv_parts = [];
            foreach ($not_voting as $nv) {
                $nv_parts[] = $nv['post_number'] !== null
                    ? $nv['username'] . ' ([post]' . $nv['post_number'] . '[/post])'
                    : $nv['username'];
            }
            $lines[] = '';
            $lines[] = '[b]Not Voting (' . count($not_voting) . ')[/b] -> ' . implode(', ', $nv_parts);
        }

        return '[area=Current Votes]' . implode("\n", $lines) . '[/area]';
    }
}
