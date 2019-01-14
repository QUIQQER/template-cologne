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

/* user avatar */
$Avatar = new QUI\FrontendUsers\Controls\UserIcon([
    'showLogout' => false, // template cologne use own logout popup (see bin/javascript/init.js)
    'User'       => QUI::getUserBySession()
]);

$Engine->assign([
    'BricksManager' => QUI\Bricks\Manager::init(),
    'Project'       => $Project,
    'Menu'          => $Menu,
    'Avatar'        => $Avatar
]);

/**
 * Template config
 */
$templateSettings = QUI\TemplateCologne\Utils::getConfig(array(
    'Project'  => $Project,
    'Site'     => $Site,
    'Template' => $Template
));

$Engine->assign($templateSettings);
