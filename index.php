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

$Engine->assign([
    'BricksManager'  => QUI\Bricks\Manager::init(),
    'typeClass'      => $typeClass,
    'Menu'           => $Menu
]);
