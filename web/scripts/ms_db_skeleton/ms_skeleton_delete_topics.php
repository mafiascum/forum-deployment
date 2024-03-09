<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

$auto_sync = true;
$post_count_sync = true;

$sql = 'SELECT topic_id
		FROM ' . TOPICS_TABLE . '
		WHERE topic_id NOT IN(
			WITH qualifying_topic AS
			(
				SELECT topic_id, forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE is_private=0
				AND topic_type=0
			),
			final_list AS
			(
				SELECT
					qualifying_topic.topic_id,
					ROW_NUMBER() OVER (PARTITION BY qualifying_topic.forum_id ORDER BY qualifying_topic.topic_id DESC) AS "topic_forum_order"
				FROM qualifying_topic
			)
			SELECT phpbb_topics.topic_id
			FROM ' . TOPICS_TABLE . '
			LEFT JOIN final_list ON(final_list.topic_id=' . TOPICS_TABLE . '.topic_id)
			WHERE final_list.topic_forum_order <= 5 OR (' . TOPICS_TABLE . '.is_private = 0 AND ' . TOPICS_TABLE . '.topic_type != 0)
		)';

$result = $db->sql_query($sql);
$topics_to_delete = array();
while($row = $db->sql_fetchrow()) {
	$topics_to_delete[] = $row['topic_id'];
}

$db->sql_freeresult($result);

$chunks = array_chunk($topics_to_delete, 100);

echo("Total # Topics To Delete: " . count($topics_to_delete) . "\n");
echo("Total # Chunks: " . count($chunks) . "\n");

$number_of_chunks = count($chunks);
foreach ($chunks as $index => $remove_topics) {
	echo("[" . ($index + 1) . "/" . $number_of_chunks . "] Deleting " . count($remove_topics) . " topic(s)...\n");
	delete_topics('topic_id', $remove_topics, $auto_sync, $post_count_sync, true);
}

echo "Process complete!\n";

?>