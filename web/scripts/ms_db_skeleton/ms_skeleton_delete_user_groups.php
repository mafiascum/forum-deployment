<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

$sql = 'SELECT ' . USER_GROUP_TABLE . '.user_id, ' . USER_GROUP_TABLE . '.group_id
		FROM ' . USER_GROUP_TABLE . ', ' . GROUPS_TABLE . '
		WHERE ' . USER_GROUP_TABLE . '.group_id=' . GROUPS_TABLE . '.group_id
		AND ' . GROUPS_TABLE . '.group_type!=' . GROUP_SPECIAL;

$group_id_to_users_map = [];
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow()) {

	$group_id = $row['group_id'];
	$user_id = $row['user_id'];
	if (isset($group_id_to_users_map[$group_id])) {
		$group_id_to_users_map[$group_id][] = $user_id;
	} else {
		$group_id_to_users_map[$group_id] = [$user_id];
	}
}

$db->sql_freeresult($result);

echo("# GROUP ENTRIES: " . count($group_id_to_users_map) . "\n");

foreach ($group_id_to_users_map as $group_id => $user_ids) {
	echo("Deleting " . count($user_ids) . " user(s) from group #" . $group_id . "...\n");
	group_user_del($group_id, $user_ids, false, false, false);
}

echo("Process complete!");
?>