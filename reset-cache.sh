#!/bin/bash

docker exec forum-deployment-web-1 sh -c "rm -f /data/forum/cache/production/url_matcher.php /data/forum/cache/production/url_matcher.php.meta && apache2ctl graceful"
