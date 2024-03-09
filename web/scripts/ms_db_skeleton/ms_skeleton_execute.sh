#!/bin/bash

FORUM_BASE_DIR="/opt/mafiascum/forum"
PRUNE_SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

if [[ "$MAFIASCUM_ENVIRONMENT" == "prod" || "$MAFIASCUM_ENVIRONMENT" == "staging" ]]; then
	echo "This process cannot be run on prod or staging environments"
	exit 1
fi

(
	cd "$FORUM_BASE_DIR"
	php "${PRUNE_SCRIPT_DIR}/ms_skeleton_delete_user_groups.php"
	php "${PRUNE_SCRIPT_DIR}/ms_skeleton_delete_topics.php"
	php "${PRUNE_SCRIPT_DIR}/ms_skeleton_delete_forums.php"
	php "${PRUNE_SCRIPT_DIR}/ms_skeleton_cleanup.php"
)
