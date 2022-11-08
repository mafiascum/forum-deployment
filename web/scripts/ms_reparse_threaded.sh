#!/bin/bash

cd /opt/bitnami/phpbb
RANGE_SIZE="10000"

php bin/phpbbcli.php reparser:reparse --range-max=3000000 --range-size="$RANGE_SIZE" post_text &
sleep 3
echo "update phpbb_config set config_value='0' where config_name='reparse_lock';" | mysql -uroot -p --host=database --password="$MYSQL_ROOT_PASSWORD" ms_phpbb3
php bin/phpbbcli.php reparser:reparse --range-min=3000000 --range-max=6000000 --range-size="$RANGE_SIZE" post_text &
sleep 3
echo "update phpbb_config set config_value='0' where config_name='reparse_lock';" | mysql -uroot -p --host=database --password="$MYSQL_ROOT_PASSWORD" ms_phpbb3
php bin/phpbbcli.php reparser:reparse --range-min=6000000 --range-max=9000000 --range-size="$RANGE_SIZE" post_text &
sleep 3
echo "update phpbb_config set config_value='0' where config_name='reparse_lock';" | mysql -uroot -p --host=database --password="$MYSQL_ROOT_PASSWORD" ms_phpbb3
php bin/phpbbcli.php reparser:reparse --range-min=9000000 --range-max=12000000 --range-size="$RANGE_SIZE" post_text &
sleep 3
echo "update phpbb_config set config_value='0' where config_name='reparse_lock';" | mysql -uroot -p --host=database --password="$MYSQL_ROOT_PASSWORD" ms_phpbb3
php bin/phpbbcli.php reparser:reparse --range-min=12000000 --range-size="$RANGE_SIZE" post_text &
wait
