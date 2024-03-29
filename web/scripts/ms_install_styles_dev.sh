#!/bin/bash
[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

install_ms_styles_dev() {
    cd /mafiascum/styles

    for file in *; do
        if [[ -d "$file" ]]; then
            rm -rf "/opt/mafiascum/forum/styles/$file"
            ln -s $(realpath "$file") "/opt/mafiascum/forum/styles/$file"
        fi
    done
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_styles_dev
fi