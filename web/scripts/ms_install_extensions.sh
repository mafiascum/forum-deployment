#!/bin/bash

set -x

cd /tmp

curl -LJO https://raw.githubusercontent.com/mafiascum/forum-extension-manifest/main/${MAFIASCUM_ENVIRONMENT:-staging}/composer.json

cd /opt/bitnami/phpbb

COMPOSER=/tmp/composer.json composer.phar install --ignore-platform-reqs
COMPOSER=/tmp/composer.json composer.phar update --ignore-platform-reqs

rm composer.json || true

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