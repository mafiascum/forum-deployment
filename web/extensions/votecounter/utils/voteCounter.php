<?php

namespace mafiascum\votecounter\utils;

class VoteCounter
{
    public static function buildMessage(array $players, array $vote_rows): string
    {
        $alive_count = count($players);
        $majority    = (int) floor($alive_count / 2) + 1;

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
                ];
            }
        }

        uasort($votes_by_target, function ($a, $b) {
            return count($b['voters']) - count($a['voters']);
        });

        $lines = [];
        foreach ($votes_by_target as $entry) {
            $count       = count($entry['voters']);
            $voter_parts = [];
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
}
