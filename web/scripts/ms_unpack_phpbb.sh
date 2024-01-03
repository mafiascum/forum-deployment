PHPBB_MAJOR_VERSION=3
PHPBB_MINOR_VERSION=3
PHPBB_PATCH_VERSION=11
PHPBB_MAJOR_MINOR="${PHPBB_MAJOR_VERSION}.${PHPBB_MINOR_VERSION}"
PHPBB_VERSION="${PHPBB_MAJOR_VERSION}.${PHPBB_MINOR_VERSION}.${PHPBB_PATCH_VERSION}"

curl -O "https://download.phpbb.com/pub/release/${PHPBB_MAJOR_MINOR}/${PHPBB_VERSION}/phpBB-${PHPBB_VERSION}.zip"
unzip phpBB-${PHPBB_VERSION}.zip
mv phpBB3 /opt/mafiascum/forum
rm phpBB-${PHPBB_VERSION}.zip

# No new install needed
rm -rf /opt/mafiascum/forum/install