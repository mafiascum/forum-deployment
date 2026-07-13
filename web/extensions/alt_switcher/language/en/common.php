<?php

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
    'ALT_SWITCHER_SWITCH_ACCOUNT'   => 'Accounts',
    'ALT_SWITCHER_ADD_ACCOUNT'      => 'Add account',
    'ALT_SWITCHER_LOG_ALL_OUT'      => 'Log all accounts out',
    'ALT_SWITCHER_ADDING_BANNER'    => 'You are adding another account. Log in below to add it to your account switcher; your previous account will remain available.',
    'ALT_SWITCHER_UNKNOWN_ALT'      => 'That account is not currently signed in on this browser.',
));
?>
