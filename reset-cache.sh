#!/bin/bash

docker compose exec web sh -c "rm -f /data/forum/cache/production/url_matcher.php /data/forum/cache/production/url_matcher.php.meta /data/forum/cache/production/url_generator.php /data/forum/cache/production/url_generator.php.meta && apache2ctl graceful"
