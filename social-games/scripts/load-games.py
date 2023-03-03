#!/usr/bin/python3

import boto3
import os
import pymysql
import subprocess
import string
import random

bucket_name=os.environ['AWS_BACKUP_BUCKET']
mysql_host=os.environ['SOCIAL_GAMES_DB_HOST']
mysql_user=os.environ['SOCIAL_GAMES_DB_USER']
mysql_password=os.environ['MYSQL_ROOT_PASSWORD']
domain_name=os.environ['SOCIAL_GAMES_DOMAIN_NAME']
http_schema=os.environ['SOCIAL_GAMES_HTTP_SCHEMA']
doc_root=os.environ['SOCIAL_GAMES_DOC_ROOT']

gameIndexFile = open('/game-index.txt', 'r')
lines = gameIndexFile.read().splitlines()
s3 = boto3.resource('s3')

def mysql_schema_exists(mysql_host, mysql_user, mysql_password, schema_name):
    try:
        with pymysql.connect(host = mysql_host, user = mysql_user, password = mysql_password, database = schema_name) as db:
            pass
    except pymysql.err.OperationalError as err:
        code, message = err.args
        if(code == 1049):
            return False
        raise
    return True

def import_database(schema_name, web_name, db_backup_s3_path):
    archive_file_path='/snapshot-restore/' + schema_name + '.sql.7z'
    s3.meta.client.download_file(bucket_name, db_backup_s3_path, archive_file_path)
    subprocess.run(["/scripts/import-db.sh", schema_name])

    with pymysql.connect(host = mysql_host, user = mysql_user, password = mysql_password, database = schema_name) as db:
        cursor = db.cursor()
        base_web_path = doc_root + '/' + web_name
        base_web_url = http_schema + domain_name + '/' + web_name

        cursor.execute("""
            UPDATE smf_settings SET value=%s WHERE variable='attachmentUploadDir'
            """, (base_web_path + '/attachments')
        )

        cursor.execute("""
            UPDATE smf_settings SET value=%s WHERE variable='smileys_dir'
            """, (base_web_path + '/Smileys')
        )

        cursor.execute("""
            UPDATE smf_settings SET value=%s WHERE variable='avatar_directory'
            """, (base_web_path + '/avatars')
        )

        cursor.execute("""
            UPDATE smf_settings SET value=%s WHERE variable='smileys_url'
            """, (base_web_url + '/Smileys')
        )

        cursor.execute("""
            UPDATE smf_settings SET value=%s WHERE variable='avatar_url'
            """, (base_web_url + '/avatars')
        )

        cursor.execute("""
            UPDATE smf_themes SET value=REGEXP_REPLACE(value, '^.*?/Themes/', %s) WHERE value LIKE '%%/Themes/%%' AND variable LIKE '%%_dir'
            """, (base_web_path + '/Themes/')
        )

        cursor.execute("""
            UPDATE smf_themes SET value=REGEXP_REPLACE(value, '^.*/Themes/', %s) WHERE value LIKE '%%/Themes/%%' AND variable LIKE '%%_url'
            """, (base_web_url + '/Themes/')
        )

        db.commit()
        cursor.close()

    if(os.path.isfile(archive_file_path)):
        os.remove(archive_file_path)


def import_web_files(web_name):
    subprocess.run(["/scripts/import-web.sh", web_name])


def update_web_config(web_name, schema_name, mysql_db_username_for_board, mysql_db_password_for_board):
    subprocess.run(["/scripts/update-settings-file.sh", web_name, schema_name, mysql_db_username_for_board, mysql_db_password_for_board])


def init_database_user(schema_name, mysql_db_username_for_board, mysql_db_password_for_board):
    with pymysql.connect(host = mysql_host, user = mysql_user, password = mysql_password, database = schema_name) as db:
        cursor = db.cursor()
        allowed_host = '%'

        cursor.execute("""
            DROP USER IF EXISTS %s@%s
            """, (mysql_db_username_for_board, allowed_host)
        )

        cursor.execute("""
            CREATE USER %s@'%%' IDENTIFIED BY %s
            """, (mysql_db_username_for_board, mysql_db_password_for_board)
        )

        cursor.execute("GRANT ALL PRIVILEGES ON " + schema_name + ".* TO %s@%s", (mysql_db_username_for_board, allowed_host))

        cursor.execute("FLUSH PRIVILEGES")

        db.commit()
        cursor.close()


def update_apache_config(web_name):
    subprocess.run(["/scripts/update-apache-config.sh", web_name])


def generate_random_string(length):
    alphanumeric = string.ascii_letters + string.digits
    return ''.join(random.choices(alphanumeric, k=length))


for line in lines:
    gameRecord = line.split('\t')
    web_name=gameRecord[0]
    schema_name=gameRecord[1]
    mysql_db_password_for_board=generate_random_string(30)
    mysql_db_username_for_board='ms_lsg_' + schema_name

    db_backup_s3_path='social-games/db/' + schema_name + '/' + schema_name + '-latest.sql.7z'
    #web_backup_s3_path='social-games/web/' + web_name + '/' + web_name + '-latest.zip'

    print('Web Dir: ' + web_name + ', DB Schema: ' + schema_name)

    database_exists = mysql_schema_exists(mysql_host, mysql_user, mysql_password, schema_name)

    if(database_exists):
        print(' - Database already exists. Skipping import.')
    else:
        print(' - Database does does not exist. Performing import.')
        import_database(schema_name, web_name, db_backup_s3_path)

    print(' - Initializing database user: ' + mysql_db_username_for_board)
    init_database_user(schema_name, mysql_db_username_for_board, mysql_db_password_for_board)

    if not os.path.exists(doc_root + '/' + web_name):
        print(" - Web directory does not exist. Restoring from snapshot.")
        import_web_files(web_name)
    else:
        print(' - Web directory already exists. Skipping import.')

    print(' - Updating web config.')
    update_web_config(web_name, schema_name, mysql_db_username_for_board, mysql_db_password_for_board)

    print(' - Updating Apache config.')
    update_apache_config(web_name)