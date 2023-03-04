# put our own templates in
envsubst < "/opt/mafiascum/apache/vhost-templates/forum.conf.tpl" | sed '/^\s*$/d' > "/etc/apache2/sites-available/forum.conf"
envsubst < "/opt/mafiascum/apache/vhost-templates/www.conf.tpl" | sed '/^\s*$/d' > "/etc/apache2/sites-available/www.conf"
envsubst < "/opt/mafiascum/apache/vhost-templates/wiki.conf.tpl" | sed '/^\s*$/d' > "/etc/apache2/sites-available/wiki.conf"
a2ensite forum
a2ensite www
a2ensite wiki
