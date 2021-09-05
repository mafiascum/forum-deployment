#!/bin/bash

cd /tmp

curl -LJO https://raw.githubusercontent.com/mafiascum/forum-extension-manifest/main/${MAFIASCUM_ENVIRONMENT:-staging}/composer.json

cd /opt/bitnami/phpbb

COMPOSER=/tmp/composer.json composer.phar install

rm composer.json || true

# no subject in reply extension from zip
cd /tmp
curl -o /tmp/extension.zip https://www.phpbb.com/customise/db/download/181996
unzip /tmp/extension.zip -d /opt/bitnami/phpbb/ext/
rm /tmp/extension.zip

# modern quote extension
cd /tmp
curl -o /tmp/extension.zip https://www.phpbb.com/customise/db/download/159701
unzip /tmp/extension.zip -d /opt/bitnami/phpbb/ext/
rm /tmp/extension.zip