###
#
# Set users style ID based on which skin they had set prior to the upgrade.
#
###
SELECT @SILVER_ID:=style_id
FROM phpbb_styles
WHERE style_name='mafSilver';

SELECT @BLACK_ID:=style_id
FROM phpbb_styles
WHERE style_name='mafBlack';

SELECT @SEPIA_ID:=style_id
FROM phpbb_styles
WHERE style_name='mafSepia';

UPDATE phpbb_users, temp_user_old_style SET
	phpbb_users.user_style=
		IF(temp_user_old_style.style_id = 4, @SILVER_ID, #If old style is mafSilver
		IF(temp_user_old_style.style_id = 6, @SEPIA_ID, #if old style is mafSepia
		@BLACK_ID ##Every other style(scuMobile, mafTigers, prosilver, mafMobile2, quilDark, mafBlack)
	))
WHERE phpbb_users.user_id=temp_user_old_style.user_id;

-- DROP TABLE IF EXISTS `temp_user_old_style`;

###
#
# Move custom profile contact fields to new table.
#
###
UPDATE `phpbb_profile_fields_data`, `phpbb_users` SET
	`phpbb_profile_fields_data`.`pf_phpbb_skype`=`phpbb_users`.`user_skype`,
	`phpbb_profile_fields_data`.`pf_phpbb_facebook`=`phpbb_users`.`user_facebook`,
	`phpbb_profile_fields_data`.`pf_phpbb_twitter`=`phpbb_users`.`user_twitter`
WHERE `phpbb_profile_fields_data`.`user_id`=`phpbb_users`.`user_id`;

# regexp_replace(regexp_replace("http://www.twitter.com/wchare", "^(?:https?://)(?:www\\.)twitter\\.com/(.*?)/?", ""), "/", "");
# regexp_replace(regexp_replace("https://www.facebook.com/will.hare.89/", "^(?:https?://)(?:www\\.)facebook\\.com/(.*?)/?", ""), "/", "");

# 

INSERT INTO `phpbb_profile_fields_data`
(
	`user_id`, 
	`pf_user_gender`, 
	`pf_user_pronoun`, 
	`pf_phpbb_interests`, 
	`pf_phpbb_occupation`, 
	`pf_phpbb_icq`, 
	`pf_phpbb_website`,
	`pf_phpbb_yahoo`,
	`pf_phpbb_location`,
	`pf_phpbb_facebook`,
	`pf_phpbb_skype`,
	`pf_phpbb_twitter`,
	`pf_phpbb_youtube`
)
SELECT
	`phpbb_users`.`user_id`,
	null,
	null,
	'',
	'',
	'',
	'',
	'',
	'',
	`phpbb_users`.`user_facebook`,
	`phpbb_users`.`user_skype`,
	`phpbb_users`.`user_twitter`,
	''
FROM `phpbb_users`
LEFT JOIN `phpbb_profile_fields_data` ON (`phpbb_profile_fields_data`.`user_id` = `phpbb_users`.`user_id`)
WHERE `phpbb_profile_fields_data`.`user_id` IS NULL;

ALTER TABLE `phpbb_users`
DROP `user_skype`,
DROP `user_facebook`,
DROP `user_twitter`;

###
#
# Set up the new team page
#
###
TRUNCATE TABLE phpbb_teampage;

INSERT INTO phpbb_teampage VALUES
(1,13637,'',1,0),
(3,4,'',2,0),
(4,13709,'',3,0),
(5,13667,'',4,0),
(6,13647,'',5,0),
(7,13696,'',6,0);

## Disable FAQ Manager module, since the code no longer exists after upgrading
UPDATE phpbb_modules SET module_enabled=0 WHERE module_langname='ACP_FAQ_MANAGER';