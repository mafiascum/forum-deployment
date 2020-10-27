#!/bin/bash

. /opt/bitnami/scripts/apache-env.sh
. /opt/bitnami/scripts/libapache.sh

# put our own templates in
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/forum.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/forum.conf"
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/www.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/www.conf"
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/wiki.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/wiki.conf"

# remove stock files
ensure_apache_app_configuration_not_exists phpbb

# ensure they still exist as empty files or the setup script breaks
# TODO - figure out something here that doesn't suck
touch "${APACHE_VHOSTS_DIR}/phpbb-vhost.conf"
touch "${APACHE_VHOSTS_DIR}/phpbb-https-vhost.conf"