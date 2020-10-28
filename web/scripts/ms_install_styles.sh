#!/bin/bash

cd /tmp

curl -LJO https://raw.githubusercontent.com/mafiascum/forum-extension-manifest/main/${MAFIASCUM_ENVIRONMENT:-staging}/styles.json

while read line
do
    split_line=($line)
    [ -d /opt/bitnami/phpbb/styles/${split_line[0]} ] && rm -rf /opt/bitnami/phpbb/styles/${split_line[0]} 
    mkdir /opt/bitnami/phpbb/styles/${split_line[0]}
    curl -o /tmp/${split_line[0]}-master.zip https://codeload.github.com/${split_line[1]}/zip/master
    unzip /tmp/${split_line[0]}-master.zip
    mv /tmp/${split_line[0]}-master/* /opt/bitnami/phpbb/styles/${split_line[0]}/
done < <( jq -rc 'to_entries | .[] | .key + " " + .value| tostring' styles.json )
