. /opt/bitnami/scripts/apache-env.sh
. /opt/bitnami/scripts/libapache.sh

render-template "${BITNAMI_ROOT_DIR}/phpbb/config.php.tpl" > "${BITNAMI_ROOT_DIR}/phpbb/config.php"