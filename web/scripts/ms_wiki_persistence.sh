#!/bin/bash

# include the persistence lib
. /opt/bitnami/scripts/libpersistence.sh

# restore / symlink only the images directory of the wiki
restore_persisted_app "wiki" "images"