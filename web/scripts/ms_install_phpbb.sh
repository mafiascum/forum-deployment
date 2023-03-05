. /opt/mafiascum/scripts/ms_lib_utils.sh
. /opt/mafiascum/scripts/ms_lib_persistence.sh

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

export PHPBB_CONF_FILE="/opt/mafiascum/forum/config.php"

info "Restoring persisted phpBB installation"
# cache was added to persistance later, so we might be restoring from a volume that doesn't have it. if so, create the directory.
# this can be removed once there are no volumes in the wild that aren't persisting this.
# do NOT call persist_app; this creates a catch 22 situation w/ the existing volumes.
mkdir -p /data/forum/cache
chmod 777 /data/forum/cache

# restore / symlink only the images directory of the wiki
restore_persisted_app "forum" "store files images cache"

chown -R daemon:root /data/forum
chown -R daemon:root /opt/mafiascum/forum/store
chown -R daemon:root /opt/mafiascum/forum/files
chown -R daemon:root /opt/mafiascum/forum/images
chown -R daemon:root /opt/mafiascum/forum/cache


db_host="$(phpbb_conf_get "\$dbhost")"
db_port="$(phpbb_conf_get "\$dbport")"
db_name="$(phpbb_conf_get "\$dbname")"
db_user="$(phpbb_conf_get "\$dbuser")"
db_pass="$(phpbb_conf_get "\$dbpasswd")"
phpbb_wait_for_db_connection "$db_host" "$db_port" "$db_name" "$db_user" "$db_pass"
info "Upgrading database schema"
php "/opt/mafiascum/forum/bin/phpbbcli.php" "db:migrate" "--safe-mode"