#!/bin/bash

# shellcheck disable=SC1091

. /opt/mafiascum/scripts/ms_lib_utils.sh

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x
set -o errexit
set -o nounset
set -o pipefail
# set -o xtrace # Uncomment this line for debugging purpose

# Start cron
printenv | perl -pe "s|(^.*?)=(.*$)|export \1\='\2'|" > /opt/mafiascum/scripts/.env.sh
chmod +x /opt/mafiascum/scripts/.env.sh
service cron start

info "** Starting phpBB setup **"
    # apache mod setup
    /opt/mafiascum/scripts/ms_apache_mods.sh
    # php setup
    /opt/mafiascum/scripts/ms_setup_php.sh
    # do pre-persistence checks here so that we can restore from database if the volume is empty. We should never be hitting is_app_initialized false in phpbb/setup,sh
    /opt/mafiascum/scripts/ms_restore_from_backup_if_necessary.sh
    # do persistence restore (symlink) on wiki site in particular
    /opt/mafiascum/scripts/ms_wiki_persistence.sh # SEMI-TODO
    # apply env vars to config.php template
    /opt/mafiascum/scripts/ms_create_config.sh
    # do normal phpbb setup
    /opt/mafiascum/scripts/ms_install_phpbb.sh
    # remove their vhosts and install our own instead
    /opt/mafiascum/scripts/ms_vhosts.sh
    # dev env setup (not done in prod)
    /opt/mafiascum/scripts/ms_install_extensions_dev.sh
    /opt/mafiascum/scripts/ms_install_styles_dev.sh
    # Perform wiki migration if needed
    /opt/mafiascum/scripts/ms_mediawiki_migrate.sh
    info "** phpBB setup finished! **"

echo ""
exec "$@"
