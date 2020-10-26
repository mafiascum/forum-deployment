-- ###
-- #
-- # This table is required, but the phpBB migration files are missing it.
-- # I think this was fixed in https://github.com/phpbb/phpbb/pull/5926
-- ###
-- CREATE TABLE `phpbb_config_text` (
-- 	`config_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
-- 	`config_value` mediumtext COLLATE utf8_bin NOT NULL,
-- 	PRIMARY KEY (`config_name`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


###
#
# Run single, consolidated alter against post table
#
###
DROP TABLE IF EXISTS `temp_post_approved`;

CREATE TABLE `temp_post_approved`(
	`post_id` int(11) unsigned not null,
	`post_approved` tinyint(3) unsigned not null,
	PRIMARY KEY(`post_id`)
) ENGINE=MyISAM;

INSERT INTO `temp_post_approved`
SELECT
	`post_id`,
	`post_approved`
FROM phpbb_posts;

ALTER TABLE `phpbb_posts`
DROP KEY `post_subject`,
DROP KEY `post_approved`,
DROP `post_approved`,
ADD `post_visibility` tinyint(3) NOT NULL DEFAULT '0',
ADD `post_delete_time` int(11) unsigned NOT NULL DEFAULT '0',
ADD `post_delete_reason` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
ADD `post_delete_user` int(10) unsigned NOT NULL DEFAULT '0',
ADD `sfs_reported` tinyint(1) unsigned NOT NULL DEFAULT 0,
ADD KEY `post_visibility` (`post_visibility`),
CHANGE `post_id` `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
CHANGE `poster_id` `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
CHANGE `post_edit_user` `post_edit_user` int(10) unsigned NOT NULL DEFAULT '0',
CHANGE `topic_id` `topic_id` int(10) unsigned NOT NULL DEFAULT '0',
ADD KEY `poster_id_topic_id`(`poster_id`,`topic_id`),
DROP KEY `post_text`,
DROP KEY `post_content`;

UPDATE `phpbb_posts`, `temp_post_approved` SET
	`phpbb_posts`.`post_visibility`=`temp_post_approved`.`post_approved`
WHERE `phpbb_posts`.`post_id`=`temp_post_approved`.`post_id`;

DROP TABLE `temp_post_approved`;


####
#
# Get the bbcodes into place
#
###

DELETE FROM phpbb_bbcodes
WHERE bbcode_tag IN(
'cell=',
'cell',
'wiki=',
'wiki',
'table=',
'table',
'spoiler=',
'spoiler',
'mech=',
'mech',
'header=',
'header',
'goto=',
'goto',
'anchor=',
'anchor',
'area=',
'area',
'chess');

INSERT INTO `phpbb_bbcodes` VALUES
(14,'area=','',0,'[area={TEXT2;optional}]{TEXT1}[/area]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <fieldset style=\"border:3px outset grey; padding:5px 10px\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em\">{TEXT2}</legend>{TEXT1}</fieldset>\n        </xsl:when>\n        <xsl:otherwise>\n                <fieldset style=\"border:3px outset grey; padding:5px 10px\">{TEXT1}</fieldset>\n        </xsl:otherwise>\n</xsl:choose>','!\\[area\\=\\{TEXT2;optional\\}\\](.*?)\\[/area\\]!ies','\'[area={TEXT2;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/area:$uid]\'','!\\[area\\=\\{TEXT2;optional\\}:$uid\\](.*?)\\[/area:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <fieldset style=\"border:3px outset grey; padding:5px 10px\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em\">{TEXT2}</legend>${1}</fieldset>\n        </xsl:when>\n        <xsl:otherwise>\n                <fieldset style=\"border:3px outset grey; padding:5px 10px\">${1}</fieldset>\n        </xsl:otherwise>\n</xsl:choose>'),
(16,'cell=','',0,'[cell={NUMBER;optional}]{TEXT}[/cell]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <td colspan=\"{NUMBER}\" style=\"border:1px solid black; padding:3px;\">{TEXT}</td>\n        </xsl:when>\n        <xsl:otherwise>\n                <td style=\"border:1px solid black; padding:3px;\">{TEXT}</td>\n        </xsl:otherwise>\n</xsl:choose>','!\\[cell\\=\\{NUMBER;optional\\}\\](.*?)\\[/cell\\]!ies','\'[cell={NUMBER;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/cell:$uid]\'','!\\[cell\\=\\{NUMBER;optional\\}:$uid\\](.*?)\\[/cell:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <td colspan=\"{NUMBER}\" style=\"border:1px solid black; padding:3px;\">${1}</td>\n        </xsl:when>\n        <xsl:otherwise>\n                <td style=\"border:1px solid black; padding:3px;\">${1}</td>\n        </xsl:otherwise>\n</xsl:choose>'),
(30,'spoiler=','Longer spoiler text: [spoiler=clue]paragraph[/spoiler]',1,'[spoiler={TEXT1;optional}]{TEXT2}[/spoiler]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <div style=\"margin:20px; margin-top:1px; margin-bottom:1px;\"><div class=\"quotetitle\"><b>Spoiler: {TEXT1}</b> <input type=\"button\" value=\"Show\" class=\"button2\" onclick=\"if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\'; this.innerText = \'\'; this.value = \'Hide\'; } else { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\'; this.innerText = \'\'; this.value = \'Show\'; }\" /></div><div class=\"quotecontent\"><div style=\"display: none;\">{TEXT2}</div></div></div>\n        </xsl:when>\n        <xsl:otherwise>\n                <div style=\"display: inline; color:#000000 !important; background:#000000 !important; padding:0px 3px;\"  title=\"This text is hidden to prevent spoilers; to reveal, highlight it with your cursor.\">{TEXT2}</div>\n        </xsl:otherwise>\n</xsl:choose>','!\\[spoiler\\=\\{TEXT1;optional\\}\\](.*?)\\[/spoiler\\]!ies','\'[spoiler={TEXT1;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/spoiler:$uid]\'','!\\[spoiler\\=\\{TEXT1;optional\\}:$uid\\](.*?)\\[/spoiler:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <div style=\"margin:20px; margin-top:1px; margin-bottom:1px;\"><div class=\"quotetitle\"><b>Spoiler: {TEXT1}</b> <input type=\"button\" value=\"Show\" class=\"button2\" onclick=\"if (this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display != \'\') { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'\'; this.innerText = \'\'; this.value = \'Hide\'; } else { this.parentNode.parentNode.getElementsByTagName(\'div\')[1].getElementsByTagName(\'div\')[0].style.display = \'none\'; this.innerText = \'\'; this.value = \'Show\'; }\" /></div><div class=\"quotecontent\"><div style=\"display: none;\">${1}</div></div></div>\n        </xsl:when>\n        <xsl:otherwise>\n                <div style=\"display: inline; color:#000000 !important; background:#000000 !important; padding:0px 3px;\"  title=\"This text is hidden to prevent spoilers; to reveal, highlight it with your cursor.\">${1}</div>\n        </xsl:otherwise>\n</xsl:choose>'),
(40,'wiki=','',0,'[wiki={TEXT1;optional}]{TEXT2}[/wiki]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a href=\"https://wiki.mafiascum.net/index.php?title={TEXT1}\" target=\"_blank\" class=\"postlink\">{TEXT2}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a href=\"https://wiki.mafiascum.net/index.php?title={TEXT2}\" target=\"_blank\" class=\"postlink\">{TEXT2}</a>\n        </xsl:otherwise>\n</xsl:choose>','!\\[wiki\\=\\{TEXT1;optional\\}\\](.*?)\\[/wiki\\]!ies','\'[wiki={TEXT1;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/wiki:$uid]\'','!\\[wiki\\=\\{TEXT1;optional\\}:$uid\\](.*?)\\[/wiki:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a href=\"https://wiki.mafiascum.net/index.php?title={TEXT1}\" target=\"_blank\" class=\"postlink\">${1}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a href=\"https://wiki.mafiascum.net/index.php?title=${1}\" target=\"_blank\" class=\"postlink\">${1}</a>\n        </xsl:otherwise>\n</xsl:choose>'),
(42,'anchor=','Anchor: [anchor=anchor name]Text to display[/anchor]',1,'[anchor={SIMPLETEXT;optional}]{TEXT}[/anchor]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a name=\"{SIMPLETEXT}\">{TEXT}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a name=\"{TEXT}\">{TEXT}</a>\n        </xsl:otherwise>\n</xsl:choose>','!\\[anchor\\=\\{SIMPLETEXT;optional\\}\\](.*?)\\[/anchor\\]!ies','\'[anchor={SIMPLETEXT;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/anchor:$uid]\'','!\\[anchor\\=\\{SIMPLETEXT;optional\\}:$uid\\](.*?)\\[/anchor:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a name=\"{SIMPLETEXT}\">${1}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a name=\"${1}\">${1}</a>\n        </xsl:otherwise>\n</xsl:choose>'),
(43,'goto=','Goto: [goto=anchor name]Link text[/goto] (only for same-post anchor links)',0,'[goto={SIMPLETEXT;optional}]{TEXT}[/goto]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a href=\"#{SIMPLETEXT}\">{TEXT}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a href=\"#{TEXT}\">{TEXT}</a>\n        </xsl:otherwise>\n</xsl:choose>','!\\[goto\\=\\{SIMPLETEXT;optional\\}\\](.*?)\\[/goto\\]!ies','\'[goto={SIMPLETEXT;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/goto:$uid]\'','!\\[goto\\=\\{SIMPLETEXT;optional\\}:$uid\\](.*?)\\[/goto:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <a href=\"#{SIMPLETEXT}\">${1}</a>\n        </xsl:when>\n        <xsl:otherwise>\n                <a href=\"#${1}\">${1}</a>\n        </xsl:otherwise>\n</xsl:choose>'),
(46,'chess','Chess code (PGN notation): [chess]moves[/chess]',0,'[chess]{TEXT}[/chess]','<textarea id=\"ChessTextareaUnset\" style=\'display: none;\'>{TEXT}</textarea>\n<script type=\'text/javascript\'>\nvar pgn4webPath = \"./pgn4web\";\nvar pgn4webTextareaIdNum;\nif (pgn4webTextareaIdNum == undefined) { pgn4webTextareaIdNum = 1; }\npgn4webTextareaId = \"pgn4web_\" + pgn4webTextareaIdNum++;\nvar textarea = document.getElementById(\"ChessTextareaUnset\");\ntextarea.id=pgn4webTextareaId;\ntextarea.value = textarea.value.replace(/<\\s*br\\s*\\/>/gi, \' \');\nmultiGamesRegexp = /\\s*\\[\\s*\\w+\\s*\"[^\"]*\"\\s*\\]\\s*[^\\s\\[\\]]+[\\s\\S]*\\[\\s*\\w+\\s*\"[^\"]*\"\\s*\\]\\s*/m;\nif (multiGamesRegexp.test(textarea.value)) { height = 500; }\nelse { height = 450; }\ndocument.write(\"<\" + \"iframe src=\'\" + pgn4webPath + \"/board.html?am=none&d=3000&ss=44&ps=d&pf=d&lcs=TtKN&dcs=LHCg&bbcs=LHCg&hm=b&hcs=mF9_&bd=c&cbcs=RZmI&ctcs=zEtr&hd=j&md=j&ih=end&tm=13&fhcs=$$$$&fhs=80p&fmcs=$$$$&fccs=v71$&hmcs=M___&fms=90p&fcs=m&cd=i&bcs=TtKN&fp=13&hl=t&fh=b&fw=p&pi=\" + pgn4webTextareaId + \"\' frameborder=0 width=100% height=\" + height + \" scrolling=\'no\' marginheight=\'0\' marginwidth=\'0\'>your web browser and/or your host do not support iframes as required to display the chessboard\" + \"<\" + \"/iframe\" + \">\");\n</script>','!\\[chess\\](.*?)\\[/chess\\]!ies','\'[chess:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/chess:$uid]\'','!\\[chess:$uid\\](.*?)\\[/chess:$uid\\]!s','<textarea id=\"ChessTextareaUnset\" style=\'display: none;\'>${1}</textarea>\n<script type=\'text/javascript\'>\nvar pgn4webPath = \"./pgn4web\";\nvar pgn4webTextareaIdNum;\nif (pgn4webTextareaIdNum == undefined) { pgn4webTextareaIdNum = 1; }\npgn4webTextareaId = \"pgn4web_\" + pgn4webTextareaIdNum++;\nvar textarea = document.getElementById(\"ChessTextareaUnset\");\ntextarea.id=pgn4webTextareaId;\ntextarea.value = textarea.value.replace(/<\\s*br\\s*\\/>/gi, \' \');\nmultiGamesRegexp = /\\s*\\[\\s*\\w+\\s*\"[^\"]*\"\\s*\\]\\s*[^\\s\\[\\]]+[\\s\\S]*\\[\\s*\\w+\\s*\"[^\"]*\"\\s*\\]\\s*/m;\nif (multiGamesRegexp.test(textarea.value)) { height = 500; }\nelse { height = 450; }\ndocument.write(\"<\" + \"iframe src=\'\" + pgn4webPath + \"/board.html?am=none&d=3000&ss=44&ps=d&pf=d&lcs=TtKN&dcs=LHCg&bbcs=LHCg&hm=b&hcs=mF9_&bd=c&cbcs=RZmI&ctcs=zEtr&hd=j&md=j&ih=end&tm=13&fhcs=$$$$&fhs=80p&fmcs=$$$$&fccs=v71$&hmcs=M___&fms=90p&fcs=m&cd=i&bcs=TtKN&fp=13&hl=t&fh=b&fw=p&pi=\" + pgn4webTextareaId + \"\' frameborder=0 width=100% height=\" + height + \" scrolling=\'no\' marginheight=\'0\' marginwidth=\'0\'>your web browser and/or your host do not support iframes as required to display the chessboard\" + \"<\" + \"/iframe\" + \">\");\n</script>'),
(47,'header=','',0,'[header={NUMBER;optional}]{TEXT}[/header]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <th colspan=\"{NUMBER}\" class=\'bbtableheader\'>{TEXT}</th>\n        </xsl:when>\n        <xsl:otherwise>\n                <th class=\'bbtableheader\'>{TEXT}</th>\n        </xsl:otherwise>\n</xsl:choose>','!\\[header\\=\\{NUMBER;optional\\}\\](.*?)\\[/header\\]!ies','\'[header={NUMBER;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/header:$uid]\'','!\\[header\\=\\{NUMBER;optional\\}:$uid\\](.*?)\\[/header:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <th colspan=\"{NUMBER}\" class=\'bbtableheader\'>${1}</th>\n        </xsl:when>\n        <xsl:otherwise>\n                <th class=\'bbtableheader\'>${1}</th>\n        </xsl:otherwise>\n</xsl:choose>'),
(48,'mech=','',0,'[mech={TEXT2;optional}]{TEXT1}[/mech]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <fieldset style=\"border:3px inset #800000; padding:5px 10px\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em\">{TEXT2}</legend>{TEXT1}</fieldset>\n        </xsl:when>\n        <xsl:otherwise>\n                <fieldset style=\"border:3px inset #800000; padding:5px 10px; color: darkred;font-size: 11px;\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em; display: none;\"></legend>{TEXT1}</fieldset>\n        </xsl:otherwise>\n</xsl:choose>','!\\[mech\\=\\{TEXT2;optional\\}\\](.*?)\\[/mech\\]!ies','\'[mech={TEXT2;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/mech:$uid]\'','!\\[mech\\=\\{TEXT2;optional\\}:$uid\\](.*?)\\[/mech:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <fieldset style=\"border:3px inset #800000; padding:5px 10px\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em\">{TEXT2}</legend>${1}</fieldset>\n        </xsl:when>\n        <xsl:otherwise>\n                <fieldset style=\"border:3px inset #800000; padding:5px 10px; color: darkred;font-size: 11px;\"><legend style=\"text-transform:uppercase; margin:0px 0.6em; padding:0em 0.33em; display: none;\"></legend>${1}</fieldset>\n        </xsl:otherwise>\n</xsl:choose>'),
(55,'table=','',0,'[table={ALNUM;optional}]{TEXT}[/table]','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <table style=\"border:1px solid black; background:#{ALNUM};\">{TEXT}</table>\n        </xsl:when>\n        <xsl:otherwise>\n                <table style=\"border:1px solid black; \">{TEXT}</table>\n        </xsl:otherwise>\n</xsl:choose>','!\\[table\\=\\{ALNUM;optional\\}\\](.*?)\\[/table\\]!ies','\'[table={ALNUM;optional}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/table:$uid]\'','!\\[table\\=\\{ALNUM;optional\\}:$uid\\](.*?)\\[/table:$uid\\]!s','<xsl:choose>\n        <xsl:when test=\"@* and string-length(normalize-space(@*)) >= 0\">\n                <table style=\"border:1px solid black; background:#{ALNUM};\">${1}</table>\n        </xsl:when>\n        <xsl:otherwise>\n                <table style=\"border:1px solid black; \">${1}</table>\n        </xsl:otherwise>\n</xsl:choose>'),
(1450,'countdown','',1,'[countdown]{TEXT}[/countdown]','<span class=\"countdown\">{TEXT}</span>','!\\[countdown\\](.*?)\\[/countdown\\]!ies','\'[countdown:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/countdown:$uid]\'','!\\[countdown:$uid\\](.*?)\\[/countdown:$uid\\]!s','<span class=\"countdown\">${1}</span>'),
(1451,'dice','',1,'[dice]{TEXT}[/dice]','<span class=\"dice-tag-original\">{TEXT}</span>','!\\[dice\\](.*?)\\[/dice\\]!ies','\'[dice:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${1}\')).\'[/dice:$uid]\'','!\\[dice:$uid\\](.*?)\\[/dice:$uid\\]!s','<span class=\"dice-tag-original\">${1}</span>'),
(1452,'post=','',1,'[post=#{NUMBER}]{TEXT2}[/post]','<a class=\"postlink post_tag\" href=\"{SERVER_PROTOCOL}{SERVER_NAME}{SCRIPT_PATH}viewtopic.php?p={NUMBER}#p{NUMBER}\">{TEXT2}</a>','!\\[post\\=#([0-9]+)\\](.*?)\\[/post\\]!ies','\'[post=#${1}:$uid]\'.str_replace(array(\"\\r\\n\", \'\\\"\', \'\\\'\', \'(\', \')\'), array(\"\\n\", \'\"\', \'&#39;\', \'&#40;\', \'&#41;\'), trim(\'${2}\')).\'[/post:$uid]\'','!\\[post\\=#([0-9]+):$uid\\](.*?)\\[/post:$uid\\]!s','<a class=\"postlink post_tag\" href=\"{SERVER_PROTOCOL}{SERVER_NAME}{SCRIPT_PATH}viewtopic.php?p=${1}#p${1}\">${2}</a>');


##TODO: Chess:
# <script type='text/javascript'>
# var pgn4webPath = "./pgn4web";
# var pgn4webTextareaIdNum;
# if (pgn4webTextareaIdNum == undefined) { pgn4webTextareaIdNum = 1; }
# pgn4webTextareaId = "pgn4web_" + pgn4webTextareaIdNum++;
# document.write("<textarea id='" + pgn4webTextareaId +"' style='display: none;'>{TEXT}<" + "/" + "textarea" + ">");
# document.getElementById(pgn4webTextareaId).value = document.getElementById(pgn4webTextareaId).value.replace(/<\s*br\s*\/>/gi, ' ');
# multiGamesRegexp = /\s*\[\s*\w+\s*"[^"]*"\s*\]\s*[^\s\[\]]+[\s\S]*\[\s*\w+\s*"[^"]*"\s*\]\s*/m;
# if (multiGamesRegexp.test(document.getElementById(pgn4webTextareaId).value)) { height = 500; }
# else { height = 450; }
# document.write("<iframe src='" + pgn4webPath + "/board.html?am=none&d=3000&ss=44&ps=d&pf=d&lcs=TtKN&dcs=LHCg&bbcs=LHCg&hm=b&hcs=mF9_&bd=c&cbcs=RZmI&ctcs=zEtr&hd=j&md=j&ih=end&tm=13&fhcs=$$$$&fhs=80p&fmcs=$$$$&fccs=v71$&hmcs=M___&fms=90p&fcs=m&cd=i&bcs=TtKN&fp=13&hl=t&fh=b&fw=p&pi=" + pgn4webTextareaId + "' frameborder=0 width=100% height=" + height + " scrolling='no' marginheight='0' marginwidth='0'>your web browser and/or your host do not support iframes as required to display the chessboard</iframe>");
# </script>


###
#
# Convert dice seed to new format
#
###

UPDATE phpbb_posts SET
	post_text=REGEXP_REPLACE(post_text, "<!--(\\d+)-->", "SEEDSTART\\1SEEDEND")
WHERE LOCATE("<!--", post_text) != 0;

UPDATE phpbb_privmsgs SET
	message_text=REGEXP_REPLACE(message_text, "<!--(\\d+)-->", "SEEDSTART\\1SEEDEND")
WHERE LOCATE("<!--", message_text) != 0;

###
#
# Record users' current styles. We'll need this after the upgrade to set their theme.
#
###
DROP TABLE IF EXISTS `temp_user_old_style`;

CREATE TABLE `temp_user_old_style`(
	`user_id` mediumint(8) unsigned not null,
	`style_id` mediumint(8) unsigned not null,
	PRIMARY KEY(`user_id`)
) ENGINE=MyISAM;

INSERT INTO `temp_user_old_style`
SELECT `user_id`, `user_style`
FROM `phpbb_users`;

ALTER TABLE `phpbb_users` change `user_old_emails` `user_old_emails` TEXT NULL DEFAULT NULL;
