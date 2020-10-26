<?php
// phpBB 3.0.x auto-generated configuration file
// Do not change anything in this file!
$dbms = 'mysqli';
$dbhost = '{{PHPBB_DATABASE_HOST}}';
$dbport = '{{PHPBB_DATABASE_PORT_NUMBER}}';
$dbname = '{{PHPBB_DATABASE_NAME}}';
$dbuser = '{{PHPBB_DATABASE_USER}}';
$dbpasswd = '{{PHPBB_DATABASE_PASSWORD}}';
$table_prefix = 'phpbb_';
$acm_type = 'file';
$load_extensions = '';

$siteChatUrl = '{{MAFIASCUM_SITE_CHAT_URL}}';
$siteChatProtocol = "site-chat";

$cacheBreaker = file_exists("cachebreaker.txt") ? file_get_contents("cachebreaker.txt") : "";

ini_set('display_errors', 'Off');

@define('PHPBB_INSTALLED', true);
// @define('DEBUG', true);
// @define('DEBUG_EXTRA', true);
?>