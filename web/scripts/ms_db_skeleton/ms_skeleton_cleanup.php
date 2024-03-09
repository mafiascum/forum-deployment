<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$sql_ary = [];

$sql_ary[] = '
UPDATE
	`phpbb_users`
SET
	`user_email`="noreply@mafiascum.net",
	`user_passchg`=0,
	`user_birthday`=" 0- 0-   0",
	`user_lastvisit`=0,
	`user_lastmark`=UNIX_TIMESTAMP(NOW()),
	`user_lastpost_time`=0,
	`user_lastpage`="",
	`user_last_confirm_key`="",
	`user_last_search`=0,
	`user_warnings`=0,
	`user_last_warning`=0,
	`user_login_attempts`=0,
	`user_inactive_time`=0,
	`user_timezone`="UTC",
	`user_dateformat`="d M Y H:i",
	`user_style`=27,
	`user_new_privmsg`=0,
	`user_unread_privmsg`=0,
	`user_last_privmsg`=0,
	`user_message_rules`=0,
	`user_emailtime`=0,
	`user_topic_show_days`=0,
	`user_topic_sortby_type`="t",
	`user_topic_sortby_dir`="d",
	`user_post_show_days`=0,
	`user_post_sortby_type`="t",
	`enterlobby`=1,
	`user_pm_welcome`=1,
	`chat_enabled`=1,
	`user_vla_till`="",
	`user_vla_start`="",
	`user_old_emails`="",
	`user_topic_preview`=1,
	`user_reminded_time`=0,
	`user_reminded`=0,
	`user_form_salt`="",
	`user_actkey`="",
	`user_options`=1919,
	`user_post_sortby_type`="t",
	`user_post_sortby_dir`="a",
	`user_ip`="",
	`user_password`=IF(user_id=5932, md5("tigers"), md5(uuid())),
	`user_newpasswd`="";
';

$sql_ary[] = 'UPDATE phpbb_config SET config_value="1ba6da0c4d3bab5f5cf31208c9792e25" WHERE config_name="plupload_salt"';
$sql_ary[] = 'UPDATE phpbb_config SET config_value="813df20d4262b6267ade50da20a0a183" WHERE config_name="rand_seed"';
$sql_ary[] = 'UPDATE phpbb_config SET config_value="forum.dev.mafiascum.net" WHERE config_name="server_name"';
$sql_ary[] = 'UPDATE phpbb_config SET config_value="fillin" WHERE config_name="recaptcha_privkey"';
$sql_ary[] = 'UPDATE phpbb_config SET config_value="fillin" WHERE config_name="recaptcha_pubkey"';

$sql_ary[] = 'TRUNCATE TABLE `old_valentines_answers`';
$sql_ary[] = 'TRUNCATE TABLE `old_valentines_questions`';
$sql_ary[] = 'TRUNCATE TABLE `old_valentines_users`';
$sql_ary[] = 'TRUNCATE TABLE `page_top_grabber_topics`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_alt_requests`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_alts`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_anon_messages`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_backup`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_backup_remote_file`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_banlist`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_bookmarks`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_drafts`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_forums_access`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_forums_track`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_forums_watch`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_invitational_participant`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_invitational_player_rating`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_log`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_login_attempts`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_factions`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_game_status`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_game_types`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_games`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_moderators`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_modifiers`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_players`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_roles`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_mafia_slots`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_notification_emails`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_notifications`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_oauth_accounts`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_oauth_states`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_oauth_tokens`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_poll_votes`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_posts_archive`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs_folder`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs_rules`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs_swl`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs_swm`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_privmsgs_to`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_qa_confirm`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_reports`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_search_results`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_search_wordlist`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_search_wordmatch`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_sessions`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_sessions_keys`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_topic_posters`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_topics_posted`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_topics_track`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_topics_watch`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_warnings`';
$sql_ary[] = 'TRUNCATE TABLE `phpbb_zebra`';
$sql_ary[] = 'TRUNCATE TABLE `siteChatConversation`';
$sql_ary[] = 'TRUNCATE TABLE `siteChatConversationMessage`';
$sql_ary[] = 'TRUNCATE TABLE `siteChatIgnore`';
$sql_ary[] = 'TRUNCATE TABLE `siteChatUserSettings`';
$sql_ary[] = 'DROP TABLE IF EXISTS `tempUserIsAlt`';
$sql_ary[] = 'DROP TABLE IF EXISTS `tempUserNumberOfPosts`';
$sql_ary[] = 'DROP TABLE IF EXISTS `temp_user_old_style`';
$sql_ary[] = 'TRUNCATE TABLE `valentines_answers`';
$sql_ary[] = 'TRUNCATE TABLE `valentines_questions`';
$sql_ary[] = 'TRUNCATE TABLE `valentines_users`';
$sql_ary[] = 'UPDATE `phpbb_user_notifications` SET `notify`=0';
$sql_ary[] = 'UPDATE `phpbb_profile_fields_data` SET `pf_numberspam`=30, `pf_textspam`="superbowl commercials",`pf_promo_emails`=NULL';
$sql_ary[] = 'UPDATE `phpbb_posts` SET `poster_ip`="127.0.0.1"';
$sql_ary[] = 'UPDATE `phpbb_users` SET `user_lastpost_time`=(SELECT IFNULL(MAX(`post_time`), 0) FROM `phpbb_posts` WHERE `poster_id`=`user_id`)';

echo("Number of Queries: " . count($sql_ary) . "\n");

foreach ($sql_ary as $index => $sql) {
	echo("Executing Query: " . $sql . "\n");
	$db->sql_query($sql);
}

?>
