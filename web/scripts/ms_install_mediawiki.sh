mkdir /opt/mafiascum/wiki
cd /tmp

MW_VERSION_FULL="1.39.1"
MW_VERSION_MAJOR=`echo "$MW_VERSION_FULL" | awk -F'.' '{print $1"."$2}'`


curl -O https://releases.wikimedia.org/mediawiki/$MW_VERSION_MAJOR/mediawiki-$MW_VERSION_FULL.tar.gz
tar xvzf mediawiki-*.tar.gz -C /opt/mafiascum/wiki --strip-components=1
mv /tmp/LocalSettings.php /opt/mafiascum/wiki/
rm -f mediawiki-*.tar.gz

#####
  #
  # Extensions
  #
#####

MW_OFFICIAL_EXTENSION_VERSION="REL1_39"

# DeleteBatch extension
[ -d /opt/mafiascum/wiki/extensions/DeleteBatch ] || mkdir /opt/mafiascum/wiki/extensions/DeleteBatch
curl -o /tmp/mediawiki-extensions-DeleteBatch-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-DeleteBatch/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-DeleteBatch-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/DeleteBatch/
mv /tmp/mediawiki-extensions-DeleteBatch-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/DeleteBatch
rm -f /tmp/mediawiki-extensions-DeleteBatch-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# Pluggable Auth extension
[ -d /opt/mafiascum/wiki/extensions/PluggableAuth ] || mkdir /opt/mafiascum/wiki/extensions/PluggableAuth
curl -o /tmp/mediawiki-extensions-PluggableAuth-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-PluggableAuth/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-PluggableAuth-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/PluggableAuth/
mv /tmp/mediawiki-extensions-PluggableAuth-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/PluggableAuth
rm -f /tmp/mediawiki-extensions-PluggableAuth-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# Auth PHPBB
[ -d /opt/mafiascum/wiki/extensions/Auth_phpBB ] || mkdir /opt/mafiascum/wiki/extensions/Auth_phpBB
curl -o /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip https://codeload.github.com/Digitalroot-Technologies/MediaWiki_PHPBB_Auth/zip/v4.1.0
unzip /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip
rm -rf /opt/mafiascum/wiki/extensions/Auth_phpBB
mv /tmp/MediaWiki_PHPBB_Auth-4.1.0 /opt/mafiascum/wiki/extensions/Auth_phpBB
rm -f /tmp/mediawiki-extensions-PHPBB_Auth-4.1.0.zip

# Nuke Extension
[ -d /opt/mafiascum/wiki/extensions/Nuke ] || mkdir /opt/mafiascum/wiki/extensions/Nuke
curl -o /tmp/mediawiki-extensions-Nuke-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-Nuke/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-Nuke-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/Nuke/
mv /tmp/mediawiki-extensions-Nuke-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/Nuke
rm -f /tmp/mediawiki-extensions-Nuke-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# Dynamic Page List
[ -d /opt/mafiascum/wiki/extensions/intersection ] || mkdir /opt/mafiascum/wiki/extensions/intersection
curl -o /tmp/mediawiki-extensions-intersection-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-intersection/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-intersection-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/intersection/
mv /tmp/mediawiki-extensions-intersection-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/intersection
rm -f /tmp/mediawiki-extensions-intersection-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# Parser Functions
[ -d /opt/mafiascum/wiki/extensions/ParserFunctions ] || mkdir /opt/mafiascum/wiki/extensions/ParserFunctions
curl -o /tmp/mediawiki-extensions-ParserFunctions-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-ParserFunctions/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-ParserFunctions-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/ParserFunctions/
mv /tmp/mediawiki-extensions-ParserFunctions-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/ParserFunctions
rm -f /tmp/mediawiki-extensions-ParserFunctions-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# WikiSEO
[ -d /opt/mafiascum/wiki/extensions/WikiSEO ] || mkdir /opt/mafiascum/wiki/extensions/WikiSEO
curl -o /tmp/mediawiki-extensions-WikiSEO-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-WikiSEO/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-WikiSEO-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/WikiSEO/
mv /tmp/mediawiki-extensions-WikiSEO-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/WikiSEO
rm -f /tmp/mediawiki-extensions-WikiSEO-"$MW_OFFICIAL_EXTENSION_VERSION".zip

# Abuse Filter
[ -d /opt/mafiascum/wiki/extensions/AbuseFilter ] || mkdir /opt/mafiascum/wiki/extensions/AbuseFilter
curl -o /tmp/mediawiki-extensions-AbuseFilter-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-extensions-AbuseFilter/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-extensions-AbuseFilter-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/extensions/AbuseFilter/
mv /tmp/mediawiki-extensions-AbuseFilter-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/extensions/AbuseFilter
rm -f /tmp/mediawiki-extensions-AbuseFilter-"$MW_OFFICIAL_EXTENSION_VERSION".zip

#####
  #
  # Skins
  #
#####

## CologneBlue
[ -d /opt/mafiascum/wiki/skins/CologneBlue ] || mkdir /opt/mafiascum/wiki/skins/CologneBlue
curl -o /tmp/mediawiki-skins-CologneBlue-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-skins-CologneBlue/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-skins-CologneBlue-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/skins/CologneBlue/
mv /tmp/mediawiki-skins-CologneBlue-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/skins/CologneBlue
rm -f /tmp/mediawiki-skins-CologneBlue-"$MW_OFFICIAL_EXTENSION_VERSION".zip

## Modern
[ -d /opt/mafiascum/wiki/skins/Modern ] || mkdir /opt/mafiascum/wiki/skins/Modern
curl -o /tmp/mediawiki-skins-Modern-"$MW_OFFICIAL_EXTENSION_VERSION".zip https://codeload.github.com/wikimedia/mediawiki-skins-Modern/zip/"$MW_OFFICIAL_EXTENSION_VERSION"
unzip /tmp/mediawiki-skins-Modern-"$MW_OFFICIAL_EXTENSION_VERSION".zip
rm -rf /opt/mafiascum/wiki/skins/Modern/
mv /tmp/mediawiki-skins-Modern-"$MW_OFFICIAL_EXTENSION_VERSION" /opt/mafiascum/wiki/skins/Modern
rm -f /tmp/mediawiki-skins-Modern-"$MW_OFFICIAL_EXTENSION_VERSION".zip
