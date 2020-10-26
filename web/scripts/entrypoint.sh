#!/bin/bash

# shellcheck disable=SC1091

set -o errexit
set -o nounset
set -o pipefail
# set -o xtrace # Uncomment this line for debugging purpose

# Load phpBB environment
. /opt/bitnami/scripts/phpbb-env.sh

# Load libraries
. /opt/bitnami/scripts/libbitnami.sh
. /opt/bitnami/scripts/liblog.sh
. /opt/bitnami/scripts/libwebserver.sh

print_welcome_page

if [[ "$1" = "/opt/bitnami/scripts/$(web_server_type)/run.sh" || "$1" = "/opt/bitnami/scripts/nginx-php-fpm/run.sh" ]]; then
    info "** Starting phpBB setup **"
    /opt/bitnami/scripts/"$(web_server_type)"/setup.sh
    /opt/bitnami/scripts/php/setup.sh
    /opt/bitnami/scripts/mysql-client/setup.sh
    # do pre-persistence checks here so that we can restore from database if the volume is empty. We should never be hitting is_app_initialized false in phpbb/setup,sh
    /opt/bitnami/scripts/mafiascum/ms_restore_from_backup_if_necessary.sh
    # do persistence restore (symlink) on wiki site in particular
    /opt/bitnami/scripts/mafiascum/ms_wiki_persistence.sh
    # apply env vars to config.php template
    /opt/bitnami/scripts/mafiascum/ms_create_config.sh
    # run any MS specific pre-migrations
    /opt/bitnami/scripts/mafiascum/ms_pre_migrations.sh
    # do normal phpbb setup
    /opt/bitnami/scripts/phpbb/setup.sh
    # remove their vhosts and install our own instead
    /opt/bitnami/scripts/mafiascum/ms_vhosts.sh
    # do any other one time setup that you want for this container
    /post-init.sh
    # install extensions from composer after all other setup is done
    /opt/bitnami/scripts/mafiascum/ms_install_extensions.sh

    info "** phpBB setup finished! **"
fi

echo ""
exec "$@"