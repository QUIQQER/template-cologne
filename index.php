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
$menuParams = [
    'showStart'                   => false,
    'data-show-button-on-desktop' => 1,
    'Project'                     => $Site->getProject()
];

if (isset($templateSettings['homeLink']) && $templateSettings['homeLink']) {
    $menuParams['showStart'] = true;
}

if (isset($templateSettings['homeLinkText']) && $templateSettings['homeLinkText'] !== '') {
    $menuParams['startText'] = $templateSettings['homeLinkText'];
}

if ($Project->getConfig('templateCologne.settings.enableIndependentMenu') && $Project->getConfig('templateCologne.settings.menuId')) {
    $menuParams['menuId']              = $Project->getConfig('templateCologne.settings.menuId');
    $menuParams['showFirstLevelIcons'] = $Project->getConfig('templateCologne.settings.showFirstLevelIcons');
    $menuParams['showStart']           = false;
}

// Site own / independent menu
if ($Site->getAttribute('templateCologne.independentMenuId')) {
    $menuParams['menuId'] = $Site->getAttribute('templateCologne.independentMenuId');
}

$Menu = new QUI\Menu\MegaMenu($menuParams);

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

$Logo       = $Project->getMedia()->getLogoImage();
$logoHeight = $templateSettings['logoHeight'];
$logoWidth  = false;

try {
    if ($Logo) {
        $logoWidth = $Logo->getResizeSize(false, $logoHeight)['width'];
    }
} catch (QUI\Exception $Exception) {
    QUI\System\Log::addNotice($Exception->getMessage());
}

$templateSettings['Logo']               = $Logo;
$templateSettings['logoHeight']         = $logoHeight;
$templateSettings['logoWidth']          = $logoWidth;
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
 * Langguage and currency swtich
 */
//$LangCurrencySwitch = new \QUI\TemplateCologne\Controls\LangCurrencySwitch();
$LangCurrencySwitch = null;



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
$templateSettings['Search']             = new QUI\ERP\Products\Search\Controls\Suggest([
    'globalsearch' => true
]);
$templateSettings['registerSiteUrl']    = $registerSiteUrl;

$Template->setAttributes($templateSettings);

$Engine->assign($templateSettings);
