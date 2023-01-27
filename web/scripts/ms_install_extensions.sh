#!/bin/bash

set -x

install_non_ms_extensions () {
    extensions=( \

        ## No Subject In Reply
        "https://www.phpbb.com/customise/db/download/181996" \

        ## Modern Quote
        "https://www.phpbb.com/customise/db/download/159701" \

        ## Stop Forum Spam
        "https://www.phpbb.com/customise/db/download/152041" \

        ## PM Search
        "https://mafiascum-files.s3.us-west-2.amazonaws.com/static/phpbb-extensions/pmsearch.zip" \

        ## Birthday Cake
        "https://www.phpbb.com/customise/db/download/183386" \

        ## PM Welcome
        "https://www.phpbb.com/customise/db/download/183901" \
    )

    for extension in "${extensions[@]}" ; do
        cd /tmp
        curl -o /tmp/extension.zip "$extension"
        unzip -o /tmp/extension.zip -d /opt/bitnami/phpbb/ext/
        rm /tmp/extension.zip
    done
}

install_ms_extensions_prod () {
    cd /opt/bitnami/phpbb
    COMPOSER=composer-extension-manifest.json composer.phar install --ignore-platform-reqs
    while read line
    do
        COMPOSER=composer-extension-manifest.json composer.phar update $line --ignore-platform-reqs
    done  < <( jq -rc '.require | keys | .[]' composer-extension-manifest.json )
}

install_ms_extensions_dev () {
    if [[ ! -e /opt/bitnami/phpbb/ext/mafiascum ]]; then
        ln -s /mafiascum/extensions /opt/bitnami/phpbb/ext/mafiascum
    fi
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_extensions_dev
else
    install_ms_extensions_prod
fi
install_non_ms_extensions