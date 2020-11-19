# forum-deployment
MS Forum Deployment Configuration 

Instructions
TODO: ensure that services can come up regardless of order

1) clone this repo in your target environment
1) copy `.env.sample` to `.env` and fill out params appropriately (sphinx_id you will not yet know)
1) `docker-compose build`
1) `docker-compose up -d database`
1) check the container logs and wait until the database starts serving connections (this may take a while the first time due to loading from S3)
1) `docker-compose up -d web`
1) check the container logs and wait until apache statts serving connections (this may take a while the first time due to running migrations)
1) `docker-compose up -d sitechat nginx`
1) log into the website and go to the ACP
1) If necessary, change the cookie domain to match the actual domain you're using
1) Deactivate the old mafSilver that exists
1) Install all styles (mafSilver, mafSepia, mafBlack)
1) make mafBlack the default style and disable proSilver
1) ensure that the anonymos user style is the new mafblack
1) exec into the web container and run `/opt/bitnami/scripts/mafiascum/ms_post_migrations.sh`
1) enable all the relevant extensions
1) exec into the web container run run `/opt/bitnami/scripts/mafiascum/ms_post_extensions.sh`
1) exec into the web container and reparse all the bbcodes by running `cd /opt/bitnami/phpbb && php bin/phpbbcli.php reparser:reparse`
1) set search engine to sphinx
1) Set the path to: /var/lib/sphinxsearch/data
1) Set the host to: sphinx
1) Set the port to: 9312
1) Set memory limit to: 0
1) produce a config file and note the sphinx_id in it (it's the nonsense alphanumeric string in source_phpbb_SPHINX_ID_main)
1) Create an index for Sphinx fulltext
1) in your `.env`, set the SPHINX_ID to that alphanumeric string you noted earlier
1) `docker-compose up -d sphinx`
1) Wait for sphinx to index...