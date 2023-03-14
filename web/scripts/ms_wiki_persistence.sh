#!/bin/bash

. /opt/mafiascum/scripts/ms_lib_persistence.sh

# restore / symlink only the images directory of the wiki
restore_persisted_app "wiki" "images"

chown -R daemon:root /opt/mafiascum/wiki/
chown -R daemon:root /data/wiki/images