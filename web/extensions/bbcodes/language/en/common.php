<?php
/**
*
* @package phpBB Extension - MafiaScum BBCodes
* @copyright (c) 2018 mafiascum.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'NOTIFICATION_TYPE_MENTION' => 'Someone mentions you',
    'NOTIFICATION_MENTION'      => '<strong>%1$s</strong> mentioned you in the topic: <strong>%2$s</strong>',
    'MENTION_USER_NOT_FOUND' => 'The user "%s" in a [mention] tag does not exist.',
    'MENTION_INVALID'        => 'A [mention] tag contains an invalid username.',
    'MENTION_MALFORMED'      => 'A [mention] tag is missing its opening or closing tag.',
));
