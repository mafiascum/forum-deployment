<?php
/**
 * Populate phpbb_banners / phpbb_banner_grants / phpbb_user_banners from a CSV.
 *
 * Usage: php populate_banners.php <input.csv> <skipped_output.csv>
 * Input CSV columns: user_id, username, rank_id, url_index, banner_url
 */

$args = array_values(array_filter(array_slice($argv, 1), fn($a) => $a !== ''));
$allow_prod = in_array('--allow-prod', $args, true) || getenv('BANNERS_ALLOW_PROD') === '1';
$args = array_values(array_filter($args, fn($a) => $a !== '--allow-prod'));

$env = (string) getenv('MAFIASCUM_ENVIRONMENT');
if ($env !== 'local' && !$allow_prod) {
    fwrite(STDERR, "ABORT: MAFIASCUM_ENVIRONMENT is '$env'. Re-run with --allow-prod (or BANNERS_ALLOW_PROD=1) to proceed.\n");
    exit(1);
}
if ($env !== 'local' && $allow_prod) {
    fwrite(STDERR, "WARNING: running against non-local environment '$env'.\n");
}

$in  = $args[0] ?? '';
$out = $args[1] ?? '';
if ($in === '' || $out === '') {
    fwrite(STDERR, "usage: populate_banners.php [--allow-prod] <input.csv> <skipped.csv>\n");
    exit(1);
}
if (!is_readable($in)) {
    fwrite(STDERR, "Cannot read input: $in\n");
    exit(1);
}

$config_path = '/opt/mafiascum/forum/config.php';
if (!file_exists($config_path)) {
    fwrite(STDERR, "phpBB config.php not found at $config_path\n");
    exit(1);
}
require $config_path;

$mysqli = @new mysqli($dbhost, $dbuser, $dbpasswd, $dbname, (int) $dbport);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "DB connect failed: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');
$prefix = $table_prefix;

function name_from_url($url) {
    $path = parse_url($url, PHP_URL_PATH) ?? $url;
    $file = basename($path);
    $file = preg_replace('/\?.*$/', '', (string) $file);
    $file = preg_replace('/\.[^.]+$/', '', (string) $file);
    return $file !== '' ? $file : $url;
}



$rows_by_user = [];
$fh = fopen($in, 'r');
$header = fgetcsv($fh);
while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 5) continue;
    [$uid, $uname, $rid, $idx, $url] = $row;
    $uid = (int) $uid;
    $idx = (int) $idx;
    $url = trim((string) $url);
    if ($uid <= 0 || $url === '') continue;
    $rows_by_user[$uid][] = [
        'username'    => $uname,
        'rank_id'     => (int) $rid,
        'url_index'   => $idx,
        'banner_url'  => $url,
    ];
}
fclose($fh);

foreach ($rows_by_user as $uid => &$rows) {
    usort($rows, fn($a, $b) => $a['url_index'] <=> $b['url_index']);
}
unset($rows);

$out_fh = fopen($out, 'w');
fputcsv($out_fh, ['user_id', 'username', 'rank_id', 'url_index', 'banner_url', 'reason']);

$existing_users = [];
$res = $mysqli->query('SELECT user_id FROM ' . $prefix . 'users');
while ($r = $res->fetch_assoc()) $existing_users[(int) $r['user_id']] = true;
$res->free();

$banners_by_url = [];
$res = $mysqli->query('SELECT banner_id, banner_name, banner_image_url, is_public FROM ' . $prefix . 'banners');
while ($r = $res->fetch_assoc()) $banners_by_url[$r['banner_image_url']] = $r;
$res->free();

$stats = ['banners_created' => 0, 'grants_added' => 0, 'active_assignments' => 0, 'skipped' => 0, 'users_touched' => 0];

foreach ($rows_by_user as $uid => $rows) {
    if (!isset($existing_users[$uid])) {
        foreach ($rows as $row) {
            fputcsv($out_fh, [$uid, $row['username'], $row['rank_id'], $row['url_index'], $row['banner_url'], 'unknown_user']);
            $stats['skipped']++;
        }
        continue;
    }

    $to_equip  = [];
    $seen_bids = [];

    foreach ($rows as $row) {
        $url    = $row['banner_url'];
        $banner = $banners_by_url[$url] ?? null;

        if (!$banner) {
            $name      = name_from_url($url);
            $is_public = (stripos($name, 'pride') !== false) ? 1 : 0;

            $stmt = $mysqli->prepare('INSERT INTO ' . $prefix . 'banners (banner_name, banner_image_url, is_public) VALUES (?, ?, ?)');
            $stmt->bind_param('ssi', $name, $url, $is_public);
            $stmt->execute();
            $new_id = $stmt->insert_id;
            $stmt->close();

            $banner = [
                'banner_id'        => $new_id,
                'banner_name'      => $name,
                'banner_image_url' => $url,
                'is_public'        => $is_public,
            ];
            $banners_by_url[$url] = $banner;
            $stats['banners_created']++;
        }

        $bid = (int) $banner['banner_id'];
        if (isset($seen_bids[$bid])) {
            continue;
        }

        if (count($to_equip) >= 3) {
            fputcsv($out_fh, [$uid, $row['username'], $row['rank_id'], $row['url_index'], $url, 'slot_limit_reached']);
            $stats['skipped']++;
            continue;
        }

        $to_equip[] = [
            'bid'       => $bid,
            'is_public' => (int) $banner['is_public'],
        ];
        $seen_bids[$bid] = true;
    }

    if (empty($to_equip)) {
        continue;
    }

    $stats['users_touched']++;

    $mysqli->query('DELETE FROM ' . $prefix . 'user_banners WHERE user_id = ' . (int) $uid);

    $slot = 0;
    foreach ($to_equip as $eq) {
        $slot++;
        $bid = $eq['bid'];

        if ($eq['is_public'] === 0) {
            $stmt = $mysqli->prepare(
                'INSERT IGNORE INTO ' . $prefix . 'banner_grants (banner_id, user_id, granted_by, created_at) VALUES (?, ?, 0, ?)'
            );
            $ts = time();
            $stmt->bind_param('iii', $bid, $uid, $ts);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $stats['grants_added']++;
            }
            $stmt->close();
        }

        $stmt = $mysqli->prepare(
            'INSERT INTO ' . $prefix . 'user_banners (user_id, banner_id, slot) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iii', $uid, $bid, $slot);
        $stmt->execute();
        $stmt->close();
        $stats['active_assignments']++;
    }
}

fclose($out_fh);

echo "Done.\n";
foreach ($stats as $k => $v) {
    echo sprintf("  %-22s %d\n", $k . ':', $v);
}
