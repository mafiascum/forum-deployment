#!/bin/bash

set -x

while read line
do
    cd /tmp
    curl -o /tmp/extension.zip "$line"
    unzip -o /tmp/extension.zip -d /opt/bitnami/phpbb/ext/
    rm /tmp/extension.zip
done < <( jq -rc '.[]' /opt/bitnami/phpbb/external-extensions.json )