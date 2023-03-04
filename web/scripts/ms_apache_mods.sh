#!/bin/bash

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

cd /tmp

curl -LJO https://raw.githubusercontent.com/apache/httpd/trunk/modules/metadata/mod_usertrack.c

apxs -ci mod_usertrack.c
a2enmod mpm_prefork
a2enmod authn_file
a2enmod authn_core
a2enmod authz_groupfile
a2enmod authz_user
a2enmod authz_core
a2enmod access_compat
a2enmod auth_basic
a2enmod socache_shmcb
a2enmod reqtimeout
a2enmod filter
a2enmod deflate
a2enmod mime
# a2enmod log_config
# a2enmod logio
a2enmod env
a2enmod headers
a2enmod setenvif
# a2enmod version
a2enmod remoteip
a2enmod proxy
a2enmod proxy_ftp
a2enmod proxy_http
# a2enmod proxy_cgi
a2enmod proxy_ajp
a2enmod proxy_balancer
a2enmod slotmem_shm
a2enmod ssl
# a2enmod unixd
a2enmod status
a2enmod autoindex
a2enmod negotiation
a2enmod dir
a2enmod alias
a2enmod rewrite
a2enmod php
a2enmod usertrack

