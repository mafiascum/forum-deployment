#!/bin/bash
set -x

install_ms_styles_prod() {

    cd /tmp

    yes | curl -LJO https://raw.githubusercontent.com/mafiascum/forum-extension-manifest/main/${MAFIASCUM_ENVIRONMENT:-staging}/styles.json

    while read line
    do
        split_line=($line)
        [ -d /opt/bitnami/phpbb/styles/${split_line[0]} ] && rm -rf /opt/bitnami/phpbb/styles/${split_line[0]} 
        mkdir /opt/bitnami/phpbb/styles/${split_line[0]}
        curl -o /tmp/${split_line[0]}-main.zip https://codeload.github.com/${split_line[1]}/zip/main
        unzip /tmp/${split_line[0]}-main.zip
        mv /tmp/${split_line[0]}-main/* /opt/bitnami/phpbb/styles/${split_line[0]}/
    done < <( jq -rc 'to_entries | .[] | .key + " " + .value| tostring' styles.json )
}

install_ms_styles_dev() {
    cd /mafiascum/styles

    for file in *; do
        if [[ -d "$file" ]] && [[ ! -e "/opt/bitnami/phpbb/styles/$file" ]]; then
            ln -s $(realpath "$file") "/opt/bitnami/phpbb/styles/$file"
        fi 
    done
}

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    install_ms_styles_dev
else
    install_ms_styles_prod
fi