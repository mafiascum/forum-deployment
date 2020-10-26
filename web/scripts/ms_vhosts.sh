#!/bin/bash

. /opt/bitnami/scripts/apache-env.sh
. /opt/bitnami/scripts/libapache.sh

# put our own templates in
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/forum.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/forum.conf"
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/www.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/www.conf"
render-template "${BITNAMI_ROOT_DIR}/scripts/apache/bitnami-templates/wiki.conf.tpl" | sed '/^\s*$/d' > "${APACHE_VHOSTS_DIR}/wiki.conf"

ensure_apache_app_configuration_not_exists phpbb