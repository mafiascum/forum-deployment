#!/bin/bash

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

install_ms_extensions_dev () {
    rm -rf /opt/mafiascum/forum/ext/mafiascum
    ln -s /mafiascum/extensions /opt/mafiascum/forum/ext/mafiascum
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_extensions_dev
fi
