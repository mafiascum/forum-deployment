#!/bin/bash
cd /tmp

curl -LJO https://raw.githubusercontent.com/mafiascum/forum-extension-manifest/main/${MAFIASCUM_ENVIRONMENT:-staging}/composer.json

cd /opt/bitnami/phpbb

COMPOSER=/tmp/composer.json composer.phar install

