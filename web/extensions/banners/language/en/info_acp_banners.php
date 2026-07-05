<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'ACP_BANNERS_TITLE'              => 'Banners',

    'BANNERS_INTRO'                  => 'Manage banners shown below user ranks. Public banners can be equipped by anyone; non-public banners require a grant.',

    'BANNERS_GLOBAL_STATE'           => 'Global banner display',
    'BANNERS_STATE_ENABLED'          => 'Enabled — banners are visible to all users.',
    'BANNERS_STATE_DISABLED'         => 'Disabled — banners are hidden site-wide.',
    'BANNERS_ENABLE'                 => 'Enable banners',
    'BANNERS_DISABLE'                => 'Disable banners',
    'BANNERS_ENABLED_ON'             => 'Banners are now enabled site-wide.',
    'BANNERS_ENABLED_OFF'            => 'Banners are now hidden site-wide.',

    'BANNERS_COL_NAME'               => 'Name',
    'BANNERS_COL_PREVIEW'            => 'Preview',
    'BANNERS_COL_PUBLIC'             => 'Public',
    'BANNERS_COL_ACTIVE'             => 'Active on',
    'BANNERS_COL_GRANTS'             => 'Grants',
    'BANNERS_COL_ACTIONS'            => 'Actions',
    'BANNERS_COL_USER'               => 'User',

    'BANNERS_NAME'                   => 'Name',
    'BANNERS_URL'                    => 'Image URL',
    'BANNERS_PUBLIC'                 => 'Public',
    'BANNERS_USERNAME'               => 'Username',

    'BANNERS_MANAGE'                 => 'Manage',
    'BANNERS_DELETE'                 => 'Delete',
    'BANNERS_BACK'                   => 'Back to list',
    'BANNERS_ADD'                    => 'Add banner',
    'BANNERS_EDIT'                   => 'Edit banner',
    'BANNERS_GRANTS'                 => 'Grants',
    'BANNERS_GRANT_ADD'              => 'Grant to user',
    'BANNERS_GRANT_REMOVE'           => 'Revoke',
    'BANNERS_GRANTS_NONE'            => 'No grants.',
    'BANNERS_NONE'                   => 'No banners yet.',

    'BANNERS_ACTIVE_ON'              => 'Active on users',
    'BANNERS_ACTIVE_NONE'            => 'No users have this banner active.',
    'BANNERS_ACTIVE_ADD'             => 'Add to user',
    'BANNERS_ACTIVE_REMOVE'          => 'Remove',
    'BANNERS_COL_SLOT'               => 'Slot',

    'BANNERS_CREATED'                => 'Banner created.',
    'BANNERS_UPDATED'                => 'Banner updated.',
    'BANNERS_DELETED'                => 'Banner deleted.',
    'BANNERS_GRANT_ADDED'            => 'Grant added.',
    'BANNERS_GRANT_REMOVED'          => 'Grant removed.',
    'BANNERS_ACTIVE_ADDED'           => 'Banner applied to user.',
    'BANNERS_ACTIVE_REMOVED'         => 'Banner removed from user.',

    'BANNERS_DELETE_WARNING'         => 'This will also remove all grants and remove the banner from every user who has it active.',
    'BANNERS_DELETE_CONFIRM_LABEL'   => 'Type the banner name to confirm',

    'BANNERS_ERR_REQUIRED'           => 'Name and image URL are required.',
    'BANNERS_ERR_NAME_MISMATCH'      => 'The typed name did not match. Banner was not deleted.',
    'BANNERS_ERR_USER_REQUIRED'      => 'Username is required.',
    'BANNERS_ERR_USER_NOT_FOUND'     => 'User &ldquo;%s&rdquo; was not found.',
    'BANNERS_ERR_INVALID'            => 'Invalid request.',
    'BANNERS_ERR_USER_HAS_BANNER'    => 'That user already has this banner.',
    'BANNERS_ERR_SLOTS_FULL'         => 'That user already has 3 banners active.',
    'BANNERS_NOT_FOUND'              => 'Banner not found.',
));
