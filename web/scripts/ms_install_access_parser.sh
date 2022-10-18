#!/bin/bash

cd /tmp

curl -LJ https://github.com/mafiascum/access-log-parser/archive/refs/heads/main.zip -o access_log_parser.zip
unzip access_log_parser.zip
rm -f access_log_parser.zip
make -C access-log-parser-main/src/
cp access-log-parser-main/bin/access_log_parser /usr/local/bin/
rm -rf access-log-parser-main
