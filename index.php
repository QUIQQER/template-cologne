<?php

/**
 * Emotion
 */

QUI\Utils\Site::setRecursiveAttribute($Site, 'image_emotion');
QUI\Utils\Site::setRecursiveAttribute($Site, 'layout');

/**
 * Header
 */
$Menu = new QUI\Menu\MegaMenu([
    'showStart'                   => false,
    'data-show-button-on-desktop' => 1,
    'Project'                     => $Site->getProject()
]);

// header logo
$EngineForMenu = QUI::getTemplateManager()->getEngine();

$EngineForMenu->assign([
    'Logo' => $Project->getMedia()->getLogoImage()
]);

$Menu->prependHTML($EngineForMenu->fetch(\dirname(__FILE__) . '/template/menu/menuPrefix.html'));
$Menu->appendHTML($EngineForMenu->fetch(\dirname(__FILE__) . '/template/menu/menuSuffix.html'));

/* user avatar */
$Avatar = new QUI\FrontendUsers\Controls\UserIcon([
    'showLogout' => false, // template cologne use own logout popup (see bin/javascript/init.js)
    'User'       => QUI::getUserBySession()
]);

/* product page - for layouts */
$productPage = false;
switch ($Site->getAttribute('type')) {
    case 'quiqqer/products:types/category':
    case 'quiqqer/products:types/search':
        $productPage = true;
        break;
};

/**
 * Flags
 */
$Flags = new QUI\Bricks\Controls\LanguageSwitches\Flags([
    'Site'      => $Site,
    'showFlags' => true,
    'showText'  => true,
    'all'       => true
]);

/**
 * Template config
 */
$templateSettings = QUI\TemplateCologne\Utils::getConfig([
    'Project'  => $Project,
    'Site'     => $Site,
    'Template' => $Template
]);


/**
 * Lang currency swtich control
 */
$LangCurrencySwitch = new \QUI\TemplateCologne\Controls\LangCurrencySwitch();


// array to assign
$templateSettings['BricksManager']      = QUI\Bricks\Manager::init();
$templateSettings['Project']            = $Project;
$templateSettings['Menu']               = $Menu;
$templateSettings['Avatar']             = $Avatar;
$templateSettings['productPage']        = $productPage;
$templateSettings['Flags']              = $Flags;
$templateSettings['LangCurrencySwitch'] = $LangCurrencySwitch;
$templateSettings['countLanguages']     = \count($Project->getLanguages());

$Engine->assign($templateSettings);
