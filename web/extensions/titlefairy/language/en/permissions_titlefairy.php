<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'ACL_A_TITLEFAIRY' => 'Can assign user ranks via the Title Fairy module',
));
