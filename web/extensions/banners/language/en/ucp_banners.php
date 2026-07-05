<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'UCP_BANNERS'                => 'Edit Banners',

    'UCP_BANNERS_INTRO'            => 'Pick which banners appear below your rank and in what order. You can equip up to three; slot 1 shows on top.',
    'UCP_BANNERS_SLOTS'            => 'Equipped banners',
    'UCP_BANNERS_SLOT'             => 'Slot',
    'UCP_BANNERS_NAME'             => 'Name',
    'UCP_BANNERS_PREVIEW'          => 'Preview',
    'UCP_BANNERS_ORDER'            => 'Order',
    'UCP_BANNERS_ADD'              => 'Add banner',
    'UCP_BANNERS_REMOVE'           => 'Remove',
    'UCP_BANNERS_MOVE_UP'          => 'Move up',
    'UCP_BANNERS_MOVE_DOWN'        => 'Move down',
    'UCP_BANNERS_NONE_EQUIPPED'    => 'No banners equipped.',
    'UCP_BANNERS_NONE_AVAILABLE'   => 'You have no banners available.',
    'UCP_BANNERS_LIMIT_REACHED'    => 'You have reached the maximum of 3 banners. Remove one to add another.',

    'UCP_BANNERS_ERR_NOT_ALLOWED'      => 'You do not have access to that banner.',
    'UCP_BANNERS_ERR_ALREADY_EQUIPPED' => 'That banner is already equipped.',
    'UCP_BANNERS_ERR_LIMIT'            => 'You cannot equip more than 3 banners.',
    'UCP_BANNERS_ERR_INVALID'          => 'Invalid request.',
));
