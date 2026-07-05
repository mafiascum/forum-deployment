<?php
/**
 * Strip <img> tags whose src matches a URL in the CSV from every phpbb_ranks.rank_title.
 * After removing an img, iteratively strip any HTML wrapper (e.g. <font>...</font>)
 * that becomes empty as a result. URLs not in the CSV are left untouched.
 *
 * Usage: php strip_rank_banners.php [--dry-run] [--allow-prod] <csv>
 * CSV format: user_id,username,rank_id,url_index,banner_url  (only banner_url column is used)
 */

$args = array_values(array_filter(array_slice($argv, 1), fn($a) => $a !== ''));
$allow_prod = in_array('--allow-prod', $args, true) || getenv('BANNERS_ALLOW_PROD') === '1';
$dry_run    = in_array('--dry-run', $args, true);
$args = array_values(array_filter($args, fn($a) => !in_array($a, ['--allow-prod', '--dry-run'], true)));

$env = (string) getenv('MAFIASCUM_ENVIRONMENT');
if ($env !== 'local' && !$allow_prod) {
    fwrite(STDERR, "ABORT: MAFIASCUM_ENVIRONMENT is '$env'. Re-run with --allow-prod (or BANNERS_ALLOW_PROD=1) to proceed.\n");
    exit(1);
}
if ($env !== 'local' && $allow_prod) {
    fwrite(STDERR, "WARNING: running against non-local environment '$env'.\n");
}

$in = $args[0] ?? '';
if ($in === '') {
    fwrite(STDERR, "usage: strip_rank_banners.php [--dry-run] [--allow-prod] <csv>\n");
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

$urls = [];
$fh = fopen($in, 'r');
fgetcsv($fh);
while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 5) continue;
    $url = trim((string) $row[4]);
    if ($url !== '') $urls[$url] = true;
}
fclose($fh);
$urls = array_keys($urls);
fwrite(STDOUT, "Loaded " . count($urls) . " unique URLs from CSV\n");
if ($dry_run) fwrite(STDOUT, "DRY-RUN mode; no writes.\n");

$patterns = array_map(function ($u) {
    return '#<img\b[^>]*\bsrc\s*=\s*["\']?' . preg_quote($u, '#') . '["\']?[^>]*>#i';
}, $urls);

$stats = ['ranks_scanned' => 0, 'ranks_modified' => 0, 'imgs_removed' => 0];

$res = $mysqli->query('SELECT rank_id, rank_title FROM ' . $prefix . 'ranks WHERE rank_title LIKE \'%<img%\'');
while ($row = $res->fetch_assoc()) {
    $stats['ranks_scanned']++;
    $rank_id  = (int) $row['rank_id'];
    $original = (string) $row['rank_title'];
    $updated  = $original;
    $removed  = 0;

    foreach ($patterns as $pat) {
        $updated = preg_replace($pat, '', $updated, -1, $c);
        $removed += (int) $c;
    }

    if ($removed === 0) continue;

    $prev = null;
    while ($updated !== $prev) {
        $prev    = $updated;
        $updated = preg_replace('#<(\w+)[^>]*>\s*</\1>#i', '', $updated);
    }
    $updated = trim($updated);

    $stats['ranks_modified']++;
    $stats['imgs_removed'] += $removed;

    fwrite(STDOUT, "rank_id={$rank_id}  removed={$removed}\n");
    fwrite(STDOUT, "  before: " . str_replace(["\r", "\n"], ' ', $original) . "\n");
    fwrite(STDOUT, "  after:  " . str_replace(["\r", "\n"], ' ', $updated)  . "\n");

    if (!$dry_run) {
        $stmt = $mysqli->prepare('UPDATE ' . $prefix . 'ranks SET rank_title = ? WHERE rank_id = ?');
        $stmt->bind_param('si', $updated, $rank_id);
        $stmt->execute();
        $stmt->close();
    }
}
$res->free();

echo "Done.\n";
foreach ($stats as $k => $v) {
    echo sprintf("  %-16s %d\n", $k . ':', $v);
}
