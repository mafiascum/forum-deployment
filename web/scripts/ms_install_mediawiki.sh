mkdir /opt/bitnami/wiki
cd /tmp
curl -O https://releases.wikimedia.org/mediawiki/1.28/mediawiki-1.28.1.tar.gz
tar xvzf mediawiki-*.tar.gz -C /opt/bitnami/wiki --strip-components=1

# Auth PHPBB
[ -d /opt/bitnami/wiki/extensions/Auth_phpBB ] || mkdir /opt/bitnami/wiki/extensions/Auth_phpBB
curl -o /tmp/mediawiki-extensions-PHPBB_Auth-master.zip https://codeload.github.com/Digitalroot/MediaWiki_PHPBB_Auth/zip/master
unzip /tmp/mediawiki-extensions-PHPBB_Auth-master.zip
mv /tmp/MediaWiki_PHPBB_Auth-master/* /opt/bitnami/wiki/extensions/Auth_phpBB/

# DeleteBatch extension
[ -d /opt/bitnami/wiki/extensions/DeleteBatch ] || mkdir /opt/bitnami/wiki/extensions/DeleteBatch
curl -o /tmp/mediawiki-extensions-DeleteBatch-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/REL1_29
unzip /tmp/mediawiki-extensions-DeleteBatch-REL1_29.zip
mv /tmp/mediawiki-extensions-DeleteBatch-REL1_29/* /opt/bitnami/wiki/extensions/DeleteBatch/

# Nuke Extension
[ -d /opt/bitnami/wiki/extensions/Nuke ] || mkdir /opt/bitnami/wiki/extensions/Nuke
curl -o /tmp/mediawiki-extensions-Nuke-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/REL1_29
unzip /tmp/mediawiki-extensions-Nuke-REL1_29.zip
mv /tmp/mediawiki-extensions-Nuke-REL1_29/* /opt/bitnami/wiki/extensions/Nuke/

# Dynamic Page List
[ -d /opt/bitnami/wiki/extensions/intersection ] || mkdir /opt/bitnami/wiki/extensions/intersection
curl -o /tmp/mediawiki-extensions-intersection-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-intersection/zip/REL1_29
unzip /tmp/mediawiki-extensions-intersection-REL1_29.zip
mv /tmp/mediawiki-extensions-intersection-REL1_29/* /opt/bitnami/wiki/extensions/intersection/

# Parser Functions
[ -d /opt/bitnami/wiki/extensions/ParserFunctions ] || mkdir /opt/bitnami/wiki/extensions/ParserFunctions
curl -o /tmp/mediawiki-extensions-ParserFunctions-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/REL1_29
unzip /tmp/mediawiki-extensions-ParserFunctions-REL1_29.zip
mv /tmp/mediawiki-extensions-ParserFunctions-REL1_29/* /opt/bitnami/wiki/extensions/ParserFunctions/

# WikiSEO
# Note - newer versions of this will require a mediawiki upgrade
[ -d /opt/bitnami/wiki/extensions/WikiSEO ] || mkdir /opt/bitnami/wiki/extensions/WikiSEO
curl -o /tmp/mediawiki-extensions-WikiSEO-1.2.2.zip https://codeload.github.com/wikimedia/mediawiki-extensions-WikiSEO/zip/1.2.2
unzip /tmp/mediawiki-extensions-WikiSEO-1.2.2.zip
mv /tmp/mediawiki-extensions-WikiSEO-1.2.2/* /opt/bitnami/wiki/extensions/WikiSEO/

# Abuse Filter
[ -d /opt/bitnami/wiki/extensions/AbuseFilter ] || mkdir /opt/bitnami/wiki/extensions/AbuseFilter
curl -o /tmp/mediawiki-extensions-AbuseFilter-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-AbuseFilter/zip/REL1_29
unzip /tmp/mediawiki-extensions-AbuseFilter-REL1_29.zip
mv /tmp/mediawiki-extensions-AbuseFilter-REL1_29/* /opt/bitnami/wiki/extensions/AbuseFilter/

# # Spam Blacklist
# TODO: was this built in at some point?
# [ -d /opt/bitnami/wiki/extensions/SpamBlacklist ] || mkdir /opt/bitnami/wiki/extensions/SpamBlacklist
# curl -o /tmp/mediawiki-extensions-SpamBlacklist-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/REL1_29
# unzip /tmp/mediawiki-extensions-SpamBlacklist-REL1_29.zip
# mv /tmp/mediawiki-extensions-SpamBlacklist-REL1_29/* /opt/bitnami/wiki/extensions/SpamBlacklist/