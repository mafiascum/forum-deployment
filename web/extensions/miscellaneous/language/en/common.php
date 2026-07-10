<?php
/**
*
* @package phpBB Extension - MafiaScum Authentication
* @copyright (c) 2017 mafiascum.net
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
	'NO_SCUMDAYS' => 'Nobody is having a scumday today!',
	'SEARCH_USER_TOPICS'		=> 'Search user\'s topics',
	'MAF_HIDE_FROM_EGOSEARCH'   => 'Hide topic',
	'MAF_UNHIDE_FROM_EGOSEARCH' => 'Unhide topic',
	'MAF_HIDE_INVALID_SCOPE'    => 'That hide option is not recognised.',
	'MAF_UCP_HIDDEN_TOPICS'     => 'Hidden topics',
	'MAF_UCP_HIDDEN_EXPLAIN'    => 'Topics you have hidden from your egosearch results. Tick the ones you want to restore and click "Unhide selected".',
	'MAF_UCP_HIDDEN_COL_TOPIC'  => 'Topic',
	'MAF_UCP_HIDDEN_COL_UNHIDE' => 'Unhide',
	'MAF_UCP_UNHIDE_SELECTED'   => 'Unhide selected',
	'MAF_UCP_NO_HIDDEN'         => 'You have not hidden any topics.',
	'MAF_UCP_HIDDEN_UPDATED'    => 'Your hidden topics list has been updated.',
	'MAF_UCP_HIDDEN_BACK'       => 'Return to hidden topics',
));
