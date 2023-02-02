#!/bin/bash
set -x

install_ms_styles_dev() {
    cd /mafiascum/styles

    for file in *; do
        rm -rf "/opt/bitnami/phpbb/styles/$file"
        ln -s $(realpath "$file") "/opt/bitnami/phpbb/styles/$file"
    done
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_styles_dev
fi