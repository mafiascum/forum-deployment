#!/bin/bash

cd /tmp

curl -LJO https://raw.githubusercontent.com/apache/httpd/trunk/modules/metadata/mod_usertrack.c

apxs -ci mod_usertrack.c