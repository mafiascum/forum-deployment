<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'ACP_TITLEFAIRY_TITLE'      => 'Title Fairy',

    'TITLEFAIRY_INTRO'          => 'Find a user by name and assign or clear their special rank. This does not grant any other permissions on the user account.',
    'TITLEFAIRY_SEARCH_USER'    => 'Find user',
    'TITLEFAIRY_USERNAME'       => 'Username',
    'TITLEFAIRY_FIND'           => 'Find',
    'TITLEFAIRY_USER_NOT_FOUND' => 'User &ldquo;%s&rdquo; was not found.',
    'TITLEFAIRY_SELECTED_USER'  => 'Selected user',
    'TITLEFAIRY_CURRENT_RANK'   => 'Current rank',
    'TITLEFAIRY_NO_RANK'        => '(no rank assigned)',
    'TITLEFAIRY_ASSIGN_RANK'    => 'Assign rank',
    'TITLEFAIRY_SELECT_RANK'    => 'Select rank',
    'TITLEFAIRY_NONE'           => '-- No rank --',
    'TITLEFAIRY_RANK_ASSIGNED'  => 'Rank assigned.',
    'TITLEFAIRY_RANK_NOT_FOUND' => 'The selected rank is not a valid special rank.',

    'LOG_TITLEFAIRY_RANK_ASSIGNED' => '<strong>Title Fairy: rank assigned</strong><br />» user: %1$s, rank_id: %2$d',
));
