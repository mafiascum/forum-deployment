#!/bin/bash

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

while read line
do
    cd /tmp
    curl -o /tmp/extension.zip "$line"
    unzip -o /tmp/extension.zip -d /opt/mafiascum/forum/ext/
    rm /tmp/extension.zip
done < <( jq -rc '.[]' /opt/mafiascum/forum/external-extensions.json )