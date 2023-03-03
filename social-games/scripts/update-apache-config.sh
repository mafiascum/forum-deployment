#!/bin/bash

WEB_NAME="$1"

echo "<Directory ${SOCIAL_GAMES_DOC_ROOT}/$WEB_NAME>" >> /etc/apache2/apache2.conf
echo "    php_admin_value open_basedir \"${SOCIAL_GAMES_DOC_ROOT}/$WEB_NAME:/tmp\"" >> /etc/apache2/apache2.conf
echo "</Directory>" >> /etc/apache2/apache2.conf