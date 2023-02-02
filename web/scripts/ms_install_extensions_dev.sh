#!/bin/bash

set -x

install_ms_extensions_dev () {
    rm -rf /opt/bitnami/phpbb/ext/mafiascum
    ln -s /mafiascum/extensions /opt/bitnami/phpbb/ext/mafiascum
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_extensions_dev
fi