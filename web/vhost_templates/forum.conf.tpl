<VirtualHost *:8080>
	ServerAdmin admin@mafiascum.net
	DocumentRoot /opt/bitnami/phpbb/
	ServerName {{FORUM_FQDN}}
	ServerAlias {{FORUM_FQDN}}
	ErrorDocument 404 /404.php

	<Directory "/opt/bitnami/phpbb/">
		Options -Indexes +FollowSymLinks -MultiViews
    	AllowOverride None
	    Require all granted
	</Directory>

	# phpBB does not properly include PHP files because of symbolic links
  	# https://github.com/bitnami/bitnami-docker-phpbb/issues/61
	Alias /bitnami/phpbb /bitnami/phpbb
	<Directory "/bitnami/phpbb">
		Options -Indexes +FollowSymLinks -MultiViews
		AllowOverride None
		Require all granted
		DirectoryIndex index.html index.php
	</Directory>
	
	Include "/opt/bitnami/apache/conf/vhosts/htaccess/phpbb-htaccess.conf"

	ErrorLog /opt/bitnami/apache/logs/error/error.log

	# Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
	LogLevel warn

	SetEnvIf Request_URI "\.gif$|\.jpg$|\.jpeg$|\.png$|\.woff$|\.js$|\.mp4$|\.webm$|\.css$|\.woff2$|\.avi$|\.mov$|\.qt$|\.wmv$|\.yuv$|\.flv$|\.swf$|\.ogg$|\.ogv$|\.amv$|\.mp4$|\.m4p$|\.bmp$|\.tiff$|\.tif$" filetype=static

	SetEnvIf filetype "^static$" is_static

	CustomLog /opt/bitnami/apache/logs/access/access.log combined env=!filetype
	CustomLog /opt/bitnami/apache/logs/access/static.log combined env=is_static

	# RewriteEngine on
	# RewriteCond %{SERVER_NAME} ={{FORUM_FQDN}}
	# RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,NE,R=permanent]

	# RewriteCond %{SERVER_NAME} ={{FORUM_FQDN}}
	# RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,R=permanent]
</VirtualHost>