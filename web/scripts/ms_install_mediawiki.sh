mkdir /opt/bitnami/wiki
cd /tmp
curl -O https://releases.wikimedia.org/mediawiki/1.28/mediawiki-1.28.1.tar.gz
tar xvzf mediawiki-*.tar.gz -C /opt/bitnami/wiki --strip-components=1

# DeleteBatch extension
[ -d /opt/bitnami/wiki/extensions/DeleteBatch ] || mkdir /opt/bitnami/wiki/extensions/DeleteBatch
curl -o /tmp/mediawiki-extensions-DeleteBatch-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/DeleteBatch" /tmp/mediawiki-extensions-DeleteBatch-REL1_29.zip

# Nuke Extension
[ -d /opt/bitnami/wiki/extensions/Nuke ] || mkdir /opt/bitnami/wiki/extensions/Nuke
curl -o /tmp/mediawiki-extensions-Nuke-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/Nuke" /tmp/mediawiki-extensions-Nuke-REL1_29.zip

# Dynamic Page List
[ -d /opt/bitnami/wiki/extensions/intersection ] || mkdir /opt/bitnami/wiki/extensions/intersection
curl -o /tmp/mediawiki-extensions-intersection-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-intersection/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/intersection" /tmp/mediawiki-extensions-intersection-REL1_29.zip

# Parser Functions
[ -d /opt/bitnami/wiki/extensions/ParserFunctions ] || mkdir /opt/bitnami/wiki/extensions/ParserFunctions
curl -o /tmp/mediawiki-extensions-ParserFunctions-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/ParserFunctions" /tmp/mediawiki-extensions-ParserFunctions-REL1_29.zip

# WikiSEO
# TODO - get this upgraded or into S3; this relies on a defunct third party extension to be available
[ -d /opt/bitnami/wiki/extensions/WikiSEO ] || mkdir /opt/bitnami/wiki/extensions/WikiSEO
curl -o /tmp/wiki-seo-1.2.1.zip https://codeload.github.com/tinymighty/wiki-seo/zip/1.2.1
unzip -d"/opt/bitnami/wiki/extensions/WikiSEO" /tmp/wiki-seo-1.2.1.zip

# Abuse Filter
[ -d /opt/bitnami/wiki/extensions/AbuseFilter ] || mkdir /opt/bitnami/wiki/extensions/AbuseFilter
curl -o /tmp/mediawiki-extensions-AbuseFilter-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-AbuseFilter/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/AbuseFilter" /tmp/mediawiki-extensions-AbuseFilter-REL1_29.zip

# Spam Blacklist
[ -d /opt/bitnami/wiki/extensions/SpamBlacklist ] || mkdir /opt/bitnami/wiki/extensions/SpamBlacklist
curl -o /tmp/mediawiki-extensions-SpamBlacklist-REL1_29.zip https://codeload.github.com/wikimedia/mediawiki-extensions-SpamBlacklist/zip/REL1_29
unzip -d"/opt/bitnami/wiki/extensions/SpamBlacklist" /tmp/mediawiki-extensions-SpamBlacklist-REL1_29.zip