<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_forums.' . $phpEx);

$acp_forums = new acp_forums();

$sql = '
SELECT
	forum_id
FROM phpbb_forums
WHERE forum_id NOT IN(
	SELECT
		phpbb_forums.forum_id
	FROM phpbb_forums, phpbb_acl_groups, phpbb_acl_options, phpbb_groups
	WHERE phpbb_acl_groups.auth_option_id=phpbb_acl_options.auth_option_id
	AND phpbb_acl_groups.auth_setting=1
	AND phpbb_forums.forum_id=phpbb_acl_groups.forum_id
	AND phpbb_groups.group_id=phpbb_acl_groups.group_id
	AND phpbb_groups.group_name="REGISTERED"
	AND phpbb_acl_options.auth_option="f_read"
	UNION
	SELECT
		phpbb_forums.forum_id
	FROM phpbb_forums, phpbb_acl_groups, phpbb_acl_roles_data, phpbb_acl_options, phpbb_groups
	WHERE phpbb_acl_groups.auth_role_id=phpbb_acl_roles_data.role_id
	AND phpbb_acl_roles_data.auth_option_id=phpbb_acl_options.auth_option_id
	AND phpbb_acl_roles_data.auth_setting=1
	AND phpbb_forums.forum_id=phpbb_acl_groups.forum_id
	AND phpbb_groups.group_id=phpbb_acl_groups.group_id
	AND phpbb_groups.group_name="REGISTERED"
	AND phpbb_acl_options.auth_option="f_read"
)
';

$forum_ids = [];
$result = $db->sql_query($sql);

while($row = $db->sql_fetchrow()) {
	$forum_ids[] = $row['forum_id'];
}

$db->sql_freeresult($result);

echo("# Forum IDs To Delete: " . count($forum_ids) . "\n");

foreach ($forum_ids as $index => $forum_id) {

        // Deleting a parent may have deleted a child. Check to see if this forum still exists.
        $sql = 'SELECT 1 FROM ' . FORUMS_TABLE . ' WHERE forum_id=' . $forum_id;
	$result = $db->sql_query($sql);
	$row_count = count($db->sql_fetchrowset());
	$db->sql_freeresult($result);

	if($row_count == 0) {
		echo("Skipping forum #" . $forum_id . " because it no longer exists.\n");
		continue;
	} else {
		echo("Deleting forum #" . $forum_id . "...\n");
		$acp_forums->delete_forum($forum_id, 'delete', 'delete', 0, 0);
	}
}

?>