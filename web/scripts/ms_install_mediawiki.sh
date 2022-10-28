mkdir /opt/bitnami/wiki
cd /tmp

MW_VERSION_FULL="1.38.4"
MW_VERSION_MAJOR=`echo "$MW_VERSION_FULL" | awk -F'.' '{print $1"."$2}'`

curl -O https://releases.wikimedia.org/mediawiki/$MW_VERSION_MAJOR/mediawiki-$MW_VERSION_FULL.tar.gz
tar xvzf mediawiki-*.tar.gz -C /opt/bitnami/wiki --strip-components=1
mv /tmp/LocalSettings.php /opt/bitnami/wiki/
rm -f mediawiki-*.tar.gz

#####
  #
  # Extensions
  #
#####

# DeleteBatch extension
[ -d /opt/bitnami/wiki/extensions/DeleteBatch ] || mkdir /opt/bitnami/wiki/extensions/DeleteBatch
curl -o /tmp/mediawiki-extensions-DeleteBatch-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/REL1_38
unzip /tmp/mediawiki-extensions-DeleteBatch-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/DeleteBatch/
mv /tmp/mediawiki-extensions-DeleteBatch-REL1_38 /opt/bitnami/wiki/extensions/DeleteBatch
rm -f /tmp/mediawiki-extensions-DeleteBatch-REL1_38.zip

# Pluggable Auth extension
[ -d /opt/bitnami/wiki/extensions/PluggableAuth ] || mkdir /opt/bitnami/wiki/extensions/PluggableAuth
curl -o /tmp/mediawiki-extensions-PluggableAuth-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-PluggableAuth/zip/REL1_38
unzip /tmp/mediawiki-extensions-PluggableAuth-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/PluggableAuth/
mv /tmp/mediawiki-extensions-PluggableAuth-REL1_38 /opt/bitnami/wiki/extensions/PluggableAuth
rm -f /tmp/mediawiki-extensions-PluggableAuth-REL1_38.zip

# Auth PHPBB
[ -d /opt/bitnami/wiki/extensions/Auth_phpBB ] || mkdir /opt/bitnami/wiki/extensions/Auth_phpBB
curl -o /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip https://codeload.github.com/Digitalroot-Technologies/MediaWiki_PHPBB_Auth/zip/v4.1.0
unzip /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip
rm -rf /opt/bitnami/wiki/extensions/Auth_phpBB
mv /tmp/MediaWiki_PHPBB_Auth-4.1.0 /opt/bitnami/wiki/extensions/Auth_phpBB
rm -f /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip

# Nuke Extension
[ -d /opt/bitnami/wiki/extensions/Nuke ] || mkdir /opt/bitnami/wiki/extensions/Nuke
curl -o /tmp/mediawiki-extensions-Nuke-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/REL1_38
unzip /tmp/mediawiki-extensions-Nuke-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/Nuke/
mv /tmp/mediawiki-extensions-Nuke-REL1_38 /opt/bitnami/wiki/extensions/Nuke
rm -f /tmp/mediawiki-extensions-Nuke-REL1_38.zip

# Dynamic Page List
[ -d /opt/bitnami/wiki/extensions/intersection ] || mkdir /opt/bitnami/wiki/extensions/intersection
curl -o /tmp/mediawiki-extensions-intersection-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-intersection/zip/REL1_38
unzip /tmp/mediawiki-extensions-intersection-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/intersection/
mv /tmp/mediawiki-extensions-intersection-REL1_38 /opt/bitnami/wiki/extensions/intersection
rm -f /tmp/mediawiki-extensions-intersection-REL1_38.zip

# Parser Functions
[ -d /opt/bitnami/wiki/extensions/ParserFunctions ] || mkdir /opt/bitnami/wiki/extensions/ParserFunctions
curl -o /tmp/mediawiki-extensions-ParserFunctions-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/REL1_38
unzip /tmp/mediawiki-extensions-ParserFunctions-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/ParserFunctions/
mv /tmp/mediawiki-extensions-ParserFunctions-REL1_38 /opt/bitnami/wiki/extensions/ParserFunctions
rm -f /tmp/mediawiki-extensions-ParserFunctions-REL1_38.zip

# WikiSEO
[ -d /opt/bitnami/wiki/extensions/WikiSEO ] || mkdir /opt/bitnami/wiki/extensions/WikiSEO
curl -o /tmp/mediawiki-extensions-WikiSEO-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-WikiSEO/zip/REL1_38
unzip /tmp/mediawiki-extensions-WikiSEO-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/WikiSEO/
mv /tmp/mediawiki-extensions-WikiSEO-REL1_38 /opt/bitnami/wiki/extensions/WikiSEO
rm -f /tmp/mediawiki-extensions-WikiSEO-REL1_38.zip

# Abuse Filter
[ -d /opt/bitnami/wiki/extensions/AbuseFilter ] || mkdir /opt/bitnami/wiki/extensions/AbuseFilter
curl -o /tmp/mediawiki-extensions-AbuseFilter-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-extensions-AbuseFilter/zip/REL1_38
unzip /tmp/mediawiki-extensions-AbuseFilter-REL1_38.zip
rm -rf /opt/bitnami/wiki/extensions/AbuseFilter/
mv /tmp/mediawiki-extensions-AbuseFilter-REL1_38 /opt/bitnami/wiki/extensions/AbuseFilter
rm -f /tmp/mediawiki-extensions-AbuseFilter-REL1_38.zip

#####
  #
  # Skins
  #
#####

## CologneBlue
[ -d /opt/bitnami/wiki/skins/CologneBlue ] || mkdir /opt/bitnami/wiki/skins/CologneBlue
curl -o /tmp/mediawiki-skins-CologneBlue-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-skins-CologneBlue/zip/REL1_38
unzip /tmp/mediawiki-skins-CologneBlue-REL1_38.zip
rm -rf /opt/bitnami/wiki/skins/CologneBlue/
mv /tmp/mediawiki-skins-CologneBlue-REL1_38 /opt/bitnami/wiki/skins/CologneBlue
rm -f /tmp/mediawiki-skins-CologneBlue-REL1_38.zip

## Modern
[ -d /opt/bitnami/wiki/skins/Modern ] || mkdir /opt/bitnami/wiki/skins/Modern
curl -o /tmp/mediawiki-skins-Modern-REL1_38.zip https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/REL1_38
unzip /tmp/mediawiki-skins-Modern-REL1_38.zip
rm -rf /opt/bitnami/wiki/skins/Modern/
mv /tmp/mediawiki-skins-Modern-REL1_38 /opt/bitnami/wiki/skins/Modern
rm -f /tmp/mediawiki-skins-Modern-REL1_38.zip
