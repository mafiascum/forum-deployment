# forum-deployment
MS Forum Deployment Configuration 

Instructions
TODO: ensure that services can come up regardless of order

## If restoring from a backup from current prod (TODO - automate this from prod to staging s3)

1) download the latest db backup from the current prod backups bucket.
1) save this archive as s3://<staging backup bucket>/db-backups/mafiascum.backup.<environment name>.db.latest.7z
1) download the latest web backup from the current prod backups bucket
1) expand this archive with the backup password from prod
1) inside of this backup, take the following: forum/store, forum/images, forum/store - put into a single archive called s3://<staging backup bucket>/web-backups/mafiascum.backup.<environment name>.forum.latest.zip, encrypted with the new environment's backup password. take the following: wiki/images - put into a single archive called s3://<staging backup bucket>/web-backups/mafiascum.backup.<environment name>.wiki.latest.zip, encrypted with the new environment's backup password.

## Always do these steps

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