<VirtualHost *:8080>
	ServerAdmin admin@mafiascum.net
	DocumentRoot /opt/mafiascum/wiki/
	ServerName {{WIKI_FQDN}}
	ServerAlias {{WIKI_FQDN}}
	ErrorDocument 404 /404.php

	<Directory "/opt/mafiascum/wiki/">
		AllowOverride All
		Require all granted
	</Directory>

	ErrorLog /etc/apache2/logs/error/error.log
	
	# Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
	LogLevel warn

	SetEnvIf Request_URI "\.gif$|\.jpg$|\.jpeg$|\.png$|\.woff$|\.js$|\.mp4$|\.webm$|\.css$|\.woff2$|\.avi$|\.mov$|\.qt$|\.wmv$|\.yuv$|\.flv$|\.swf$|\.ogg$|\.ogv$|\.amv$|\.mp4$|\.m4p$|\.bmp$|\.tiff$|\.tif$" filetype=static

	SetEnvIf filetype "^static$" is_static

    CustomLog /etc/apache2/logs/access/access.log combined env=!filetype
    CustomLog /etc/apache2/logs/access/raw/access-file.log combined env=!filetype
	CustomLog /etc/apache2/logs/access/static.log combined env=is_static
    CustomLog /etc/apache2/logs/access/raw/static-file.log combined env=is_static

	# RewriteEngine on
	# RewriteCond %{SERVER_NAME} ={{WIKI_FQDN}}
	# RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,NE,R=permanent]

    # RewriteCond %{SERVER_NAME} ={{WIKI_FQDN}}
    # RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,R=permanent]
</VirtualHost>