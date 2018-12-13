<?php

/**
 * Emotion
 */

QUI\Utils\Site::setRecursivAttribute($Site, 'image_emotion');
QUI\Utils\Site::setRecursivAttribute($Site, 'layout');

/**
 * Header
 */
$Menu = new QUI\Menu\MegaMenu([
    'showStart' => false,
    'Project'   => $Site->getProject()
]);

$typeClass = 'type-' . str_replace(['/', ':'], '-', $Site->getAttribute('type'));


/* user avatar */
$Avatar = new QUI\FrontendUsers\Controls\UserIcon([
    'showLogout' => false, // template cologne use own logout popup (see bin/javascript/init.js)
    'User'       => QUI::getUserBySession()
]);

$Engine->assign([
    'BricksManager' => QUI\Bricks\Manager::init(),
    'Project'       => $Project,
    'typeClass'     => $typeClass,
    'Menu'          => $Menu,
    'Avatar'        => $Avatar
]);
