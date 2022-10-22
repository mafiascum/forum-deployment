#!/bin/bash
source /opt/bitnami/scripts/mafiascum/.env.sh
/opt/bitnami/apache/bin/apachectl -k graceful >> /tmp/postrotate.txt 2>&1