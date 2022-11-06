# forum-deployment
MS Forum Deployment Configuration 

Instructions

## AWS Setup that needs to be done before bringing an environment up
1) You will need to create an IAM user for application support. Call this `mafiascum-app`. 
    1) This user needs only "programmatic access"; no console access required.
    1) As of now, this needs the "AmazonS3FullAccess" existing policy and nothing else.
    1) Copy down the access key ID and secret access key - you will need them when you set up `.env`
1) Go to S3 app. Create an S3 bucket for backups. Convention is: `mafiascum-<env>-backups`
    1) No versioning
    1) No public access
1) Next, set up SMTP with SES (go to "Simple Email Service" app)
    1) Create a verified identity on the "Verified Identities" tab:
        1) Use a "Domain" type entity
        1) domain should be the root domain where the app lives: e.g. `mafiascum.net`
        1) check "use a custom MAIL FROM Domain"
        1) set the subdomain value to "mailer"
        1) uncheck "Publish DNS records to Route53" unless you are using Route53
    1) At this point, you will be returned to the details screen for this identity, and you will need to do some work on your DNS provider
        1) In order to verify the identity, you will need to create three CNAME records, listed below the pending identity to be verified. Do this, and wait for the status to change to verified.
        1) In order to verify the MAIL FROM domain (scroll down a bit) you will need to create an MX record and either an SPF or TXT record, both shown below. Use SPF if your DNS provider allows it, but otherwise you can use TXT. 
            * Ensure that your DNS records look correct; some DNS providers may only want you to put the subdomains in the "name" field. You don't want to end up with mafiascum.net.mafiascum.net in your name fields
    1) Now, create SMTP credentials - go to "SMTP Settings"
        1) "Create SMTP Credentials"
        1) set the IAM user name to `mafiascum-smtp`
        1) Click "Show user SMTP credentials"
        1) Copy these down - you will need to plug them into PhpBB later

## If restoring from a backup from current prod (TODO - automate this from prod to staging s3)

1) download the latest db backup from the current prod backups bucket.
1) save this archive as `s3://<staging backup bucket>/db-backups/mafiascum.backup.<environment name>.db.latest.7z`
1) download the latest web backup from the current prod backups bucket
1) expand this archive with the backup password from prod
1) inside of this backup, take the following: forum/store, forum/images, forum/files - put into a single archive called `s3://<staging backup bucket>/web-backups/mafiascum.backup.<environment name>.forum.latest.zip`, encrypted with the new environment's backup password. take the following: wiki/images - put into a single archive called `s3://<staging backup bucket>/web-backups/mafiascum.backup.<environment name>.wiki.latest.zip`, encrypted with the new environment's backup password.

## Always do these steps

1) clone this repo in your target environment
1) copy `.env.sample` to `.env` and fill out params appropriately (sphinx_id you will not yet know)
1) ensure docker is installed on the system. Anecdotally, snap doesn't work well with docker-compose; better to use apt or yum.
1) `docker-compose build`
1) `docker-compose up -d database`
1) check the container logs and wait until the database starts serving connections (this may take a while the first time due to loading from S3)
1) `docker-compose up -d web`
1) check the container logs and wait until apache starts serving connections (this may take a while the first time due to running migrations)
1) `docker-compose up -d sitechat nginx`
1) log into the website and go to the ACP
1) If necessary, change the cookie domain to match the actual domain you're using
1) Set cookies to secure
1) change the board's default style to something other than mafSilver
1) Run the following SQL query to rename the old mafSilver theme to avoid name collision with the new theme: ```UPDATE `phpbb_styles` SET `style_name`='mafSilverOld' WHERE `style_name`='mafSilver' AND `style_parent_tree`='';```
1) Install all styles (mafSilver, mafSepia, mafBlack)
1) make mafBlack the default style and disable proSilver
1) ensure that the anonymos user style is the new mafblack
1) exec into the web container and run `/opt/bitnami/scripts/mafiascum/ms_post_migrations.sh`
1) enable all the relevant extensions
1) exec into the db container, get a db terminal, and run the sql in https://github.com/mafiascum/forum-deployment/blob/main/web/forum/migration/db/data/after_extensions.sql
1) exec into the web container and reparse all the bbcodes by running `cd /opt/bitnami/phpbb && php bin/phpbbcli.php reparser:reparse`
1) set search engine to sphinx
1) Set the path to: /var/lib/sphinxsearch/data
1) Set the host to: sphinx
1) Set the port to: 9312
1) Set memory limit to: 0
1) produce a config file and note the sphinx_id in it (it's the nonsense alphanumeric string in source_phpbb_SPHINX_ID_main)
1) Create an index for Sphinx fulltext
1) While in here, go to the email settings: 
    * server: email-smtp.us-east-1.amazonaws.com
    * port: 587
    * username / password: mafiascum-smtp IAM credentials you created above
1) in your `.env`, set the SPHINX_ID to that alphanumeric string you noted earlier
1) `docker-compose up -d sphinx`
1) exec into the web container and run `python3 /opt/bitnami/scripts/mafiascum/reparse_quotes.py`
1) Wait for sphinx to index and the reparse_quotes to finish...

## To use this setup for extension/style development
In addition to the above steps:
1) `cp docker-compose.override.yml.sample docker-compose.override.yml`
1) `git clone` all the mafia extensions/styles into the `dev-extensions` and `dev-styles` directories, respectively
1) ensure that your `MAFIASCUM_ENVIRONMENT` env var is named one of the following: `development`, `dev`, or `local`
1) start containers. You should have volumes mounted in the `web` container for extensions and styles at `/mafiascum` and they should be symlinked to the proper phpbb locations. one symlink for all extensions since we can just symlink the mafiascum namespace, but styles need one symlink per style since they live alongside non-ms styles. No ms extensions/styles will be pulled automatically, so you should ensure you've cloned everything it needs to run, even stuff you're not actively working on.
1) you will get a certficiate warning in your browser for using a self-signed cert (included in this project). You can safely move past this.

## Useful commands
To restart the web container without impacting any volumes: `docker-compose stop web && docker-compose rm -f web && docker-compose build web && docker-compose up -d web`