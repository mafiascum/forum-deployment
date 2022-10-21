cd /tmp
curl -LJ 'https://github.com/ornicar/pgn4web/archive/refs/heads/master.zip' -o pgn4web.zip
unzip pgn4web.zip
rm -f pgn4web.zip
mv pgn4web-master /opt/bitnami/phpbb/pgn4web