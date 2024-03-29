#!/bin/bash

export APACHE_LOGS_DIR="/etc/apache2/logs"

mkdir ${APACHE_LOGS_DIR}
mkdir ${APACHE_LOGS_DIR}/access
mkdir ${APACHE_LOGS_DIR}/access/raw
mkdir ${APACHE_LOGS_DIR}/error

ln -sf "/dev/stdout" "${APACHE_LOGS_DIR}/access/access.log"
ln -sf "/dev/stdout" "${APACHE_LOGS_DIR}/access/static.log"
ln -sf "/dev/stderr" "${APACHE_LOGS_DIR}/error/error.log"