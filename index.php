<?php

/**
 * Emotion
 */

QUI\Utils\Site::setRecursiveAttribute($Site, 'image_emotion');
QUI\Utils\Site::setRecursiveAttribute($Site, 'layout');

/**
 * Template config
 */
$templateSettings = QUI\TemplateCologne\Utils::getConfig([
    'Project' => $Project,
    'Site'    => $Site,
]);

/**
 * Menu
 */
$homeLink     = false;
$homeLinkText = false;

if (isset($templateSettings['homeLink']) && $templateSettings['homeLink']) {
    $homeLink = true;
}

if (isset($templateSettings['homeLinkText']) && $templateSettings['homeLinkText'] !== '') {
    $homeLinkText = $templateSettings['homeLinkText'];
}

$Menu = new QUI\Menu\MegaMenu([
    'showStart'                   => $homeLink,
    'startText'                   => $homeLinkText,
    'data-show-button-on-desktop' => 1,
    'Project'                     => $Site->getProject()
]);

/**
 * Basket button
 */
$Currency = QUI\ERP\Currency\Handler::getUserCurrency();

if (!$Currency) {
    $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();
}

$createBasketButton = true;

if ($Site->getAttribute('type') == 'quiqqer/order:types/orderingProcess' ||
    $Site->getAttribute('type') == 'quiqqer/order:types/shoppingCart') {
    $createBasketButton = false;
}

$InitialBasketPrice = new QUI\ERP\Money\Price(0, $Currency);

$templateSettings['Logo']               = $Project->getMedia()->getLogoImage();
$templateSettings['initialBasketPrice'] = $InitialBasketPrice->getDisplayPrice();
$templateSettings['createBasketButton'] = $createBasketButton;

/* user avatar */
$Avatar = new QUI\FrontendUsers\Controls\UserIcon([
    'showLogout' => false, // template cologne use own logout popup (see bin/javascript/init.js)
    'User'       => QUI::getUserBySession()
]);

/* product page - for layouts */
$productPage = false;
switch ($Site->getAttribute('type')) {
    case 'quiqqer/products:types/category':
    case 'quiqqer/productsearch:types/search':
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
 * Lang currency swtich control
 */
$LangCurrencySwitch = new \QUI\TemplateCologne\Controls\LangCurrencySwitch();

/**
 * Sign up / registration page
 */
$registerSiteUrl = false;

$types = [
    'quiqqer/frontend-users:types/registrationSignUp',
    'quiqqer/frontend-users:types/registration',
];

$registerSite = $Project->getSites([
    'where' => [
        'type' => [
            'type'  => 'IN',
            'value' => $types
        ]
    ],
    'limit' => 1
]);


if (count($registerSite)) {
    $registerSiteUrl = $registerSite[0]->getUrlRewritten();
}

// array to assign
$templateSettings['BricksManager']      = QUI\Bricks\Manager::init();
$templateSettings['Project']            = $Project;
$templateSettings['Menu']               = $Menu;
$templateSettings['Avatar']             = $Avatar;
$templateSettings['productPage']        = $productPage;
$templateSettings['Flags']              = $Flags;
$templateSettings['LangCurrencySwitch'] = $LangCurrencySwitch;
$templateSettings['countLanguages']     = \count($Project->getLanguages());
$templateSettings['Search']             = new QUI\ERP\Products\Controls\Search\Suggest([
    'globalsearch' => true
]);
$templateSettings['registerSiteUrl']    = $registerSiteUrl;


$Engine->assign($templateSettings);
