<VirtualHost *:8080>
    ServerAdmin admin@mafiascum.net
    DocumentRoot /opt/bitnami/www/
    ServerName {{WWW_FQDN}}
    ServerAlias {{WWW_FQDN}} {{ROOT_FQDN}}
    ErrorDocument 404 /404.php

    <Directory "/opt/bitnami/www/">
            AllowOverride All
            Require all granted
    </Directory>

    ErrorLog /opt/bitnami/apache/logs/error/error.log

    # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
    LogLevel warn

    SetEnvIf Request_URI "\.gif$|\.jpg$|\.jpeg$|\.png$|\.woff$|\.js$|\.mp4$|\.webm$|\.css$|\.woff2$|\.avi$|\.mov$|\.qt$|\.wmv$|\.yuv$|\.flv$|\.swf$|\.ogg$|\.ogv$|\.amv$|\.mp4$|\.m4p$|\.bmp$|\.tiff$|\.tif$" filetype=static

    SetEnvIf filetype "^static$" is_static

    CustomLog /opt/bitnami/apache/logs/access/access.log combined env=!filetype
    CustomLog /opt/bitnami/apache/logs/access/access-file.log combined env=!filetype
	CustomLog /opt/bitnami/apache/logs/access/static.log combined env=is_static
    CustomLog /opt/bitnami/apache/logs/access/static-file.log combined env=is_static

    # RewriteEngine on
    # RewriteCond %{SERVER_NAME} ={{ROOT_FQDN}} [OR]
    # RewriteCond %{SERVER_NAME} ={{WWW_FQDN}}
    # RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,NE,R=permanent]

    # RewriteRule ^/archive/(.*) https://%{FORUM_FQDN}/$1 [NC,NE,QSA,L,R=301]

    # RewriteCond %{SERVER_NAME} ={{ROOT_FQDN}} [OR]
    # RewriteCond %{SERVER_NAME} ={{WWW_FQDN}}
    # RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,QSA,R=permanent]
</VirtualHost>