<?php

/**
 * Emotion
 */

QUI\Utils\Site::setRecursivAttribute($Site, 'image_emotion');
QUI\Utils\Site::setRecursivAttribute($Site, 'layout');

/**
 * Header
 */

$Menu = new QUI\Menu\MegaMenu(array(
    'showStart' => true,
    'Project'   => $Site->getProject()
));

$typeClass = 'type-'.str_replace(array('/', ':'), '-', $Site->getAttribute('type'));


$Engine->assign(array(
    'typeClass' => $typeClass,
    'Menu'      => $Menu
));
