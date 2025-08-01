<?php

/**
 * This file contains QUI\TemplateCologne\Utils
 */

namespace QUI\TemplateCologne;

use QUI;
use QUI\Database\Exception;
use QUI\ERP\Shipping\Shipping;
use QUI\ERP\StockManagement\StockManager;
use QUI\Projects\Project;
use QUI\TemplateCologne\Controls\Payments;
use ReflectionClass;

use function class_exists;
use function count;
use function method_exists;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Get user avatar. If no avatar available return false.
     *
     * @param $User QUI\Interfaces\Users\User
     *
     * @return QUI\Projects\Media\Image|false
     * @throws QUI\Exception
     *
     */
    public static function getAvatar(mixed $User): QUI\Projects\Media\Image | bool
    {
        if (!$User instanceof QUI\Interfaces\Users\User) {
            throw new QUI\Exception([
                QUI::getLocale()->get(
                    'quiqqer/template-cologne',
                    'exception.user.required'
                )
            ]);
        }

        $result = QUI::getEvents()->fireEvent('userGetAvatar', [$User]);

        foreach ($result as $Entry) {
            if ($Entry instanceof QUI\Projects\Media\Image) {
                return $Entry;
            }
        }

        $avatar = $User->getAttribute('avatar');

        if (!QUI\Projects\Media\Utils::isMediaUrl($avatar)) {
            return false;
        }

        try {
            return QUI\Projects\Media\Utils::getImageByUrl($avatar);
        } catch (QUI\Exception) {
        }

        return false;
    }

    /**
     * Returns config. If a cache exists, it will be returned.
     *
     * @param array $params
     *
     * @return array|bool|object|string
     * @throws QUI\Exception
     */
    public static function getConfig(array $params): object | array | bool | string
    {
        $Site = $params['Site'];
        $Project = $params['Project'];
        $Template = $params['Template'];

        $cacheName = md5($Site->getId() . $Project->getName() . $Project->getLang());

        try {
            return QUI\Cache\Manager::get(
                'quiqqer/templateCologne/' . $cacheName
            );
        } catch (QUI\Exception $Exception) {
        }

        $config = [];

        $lang = $Project->getLang();

        /**
         * Logo height
         */
        $logoHeight = 60;

        if (intval($Project->getConfig('templateCologne.settings.logoHeight'))) {
            $logoHeight = intval($Project->getConfig('templateCologne.settings.logoHeight'));
        }

        /**
         * no header?
         * no breadcrumb?
         * Body Class
         *
         * own site type
         */
        $header = 'hide';
        $pageTitle = 'breadcrumb'; // where to show page title: in header, in breadcrumb or both?
        $pageShortDesc = false;
        $showBreadcrumb = false;
        $showTopBar = true;
        $showNav = true;
        $showFooter = true;
        $minimalDesign = false;
        $siteType = 'no-sidebar';

        switch ($Site->getAttribute('layout')) {
            case 'layout/startPage':
                $header = $Project->getConfig('templateCologne.settings.headerStartPage');
                $pageTitle = $Project->getConfig('templateCologne.settings.pageTitleStartPage');
                $pageShortDesc = $Project->getConfig('templateCologne.settings.shortDescStartPage');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbStartPage');
                $siteType = 'layout-start-page';
                break;

            case 'layout/noSidebar':
                $header = $Project->getConfig('templateCologne.settings.headerNoSidebar');
                $pageTitle = $Project->getConfig('templateCologne.settings.pageTitleNoSidebar');
                $pageShortDesc = $Project->getConfig('templateCologne.settings.shortDescNoSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebar');
                $siteType = 'layout-no-sidebar';
                break;

            case 'layout/noSidebarThin':
                $header = $Project->getConfig('templateCologne.settings.headerNoSidebarThin');
                $pageTitle = $Project->getConfig('templateCologne.settings.pageTitleNoSidebarThin');
                $pageShortDesc = $Project->getConfig('templateCologne.settings.shortDescNoSidebarThin');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebarThin');
                $siteType = 'layout-no-sidebar';
                break;

            case 'layout/rightSidebar':
                $header = $Project->getConfig('templateCologne.settings.headerRightSidebar');
                $pageTitle = $Project->getConfig('templateCologne.settings.pageTitleRightSidebar');
                $pageShortDesc = $Project->getConfig('templateCologne.settings.shortDescLeftSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbRightSidebar');
                $siteType = 'layout-right-sidebar';
                break;

            case 'layout/leftSidebar':
                $header = $Project->getConfig('templateCologne.settings.headerLeftSidebar');
                $pageTitle = $Project->getConfig('templateCologne.settings.pageTitleLeftSidebar');
                $pageShortDesc = $Project->getConfig('templateCologne.settings.shortDescRightSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbLeftSidebar');
                $siteType = 'layout-left-sidebar';
                break;
        }

        $orderSiteTypes = [
            'quiqqer/order:types/orderingProcess',
            'quiqqer/order:types/shoppingCart',
            'quiqqer/order-simple-checkout:types/simpleCheckout'
        ];

        if (in_array($Site->getAttribute('type'), $orderSiteTypes)) {
            switch ($Project->getConfig('templateCologne.settings.checkoutAppearance')) {
                case 'compact':
                    $showBreadcrumb = false;
                    break;

                case 'full':
                    $showBreadcrumb = true;
                    break;

                case 'minimal':
                default:
                    $showTopBar = false;
                    $showNav = false;
                    $showFooter = false;
                    $showBreadcrumb = false;
                    $minimalDesign = true;
                    break;
            }
        }

        // Show header & footer only if requested (support for quiqqer/app)
        if (!$Template->getAttribute('template-header')) {
            $showTopBar = false;
            $showNav = false;
        }

        if (!$Template->getAttribute('template-footer')) {
            $showFooter = false;
        }

        /* site own show header */
        switch ($Site->getAttribute('templateCologne.header')) {
            case 'afterNav':
            case 'beforeContent':
            case 'hide':
                $header = $Site->getAttribute('templateCologne.header');
        }

        /* site own page title settings */
        switch ($Site->getAttribute('templateCologne.pageTitle')) {
            case 'header':
            case 'breadcrumb':
            case 'both':
            case 'disable':
                $pageTitle = $Site->getAttribute('templateCologne.pageTitle');
        }

        /* site own page description settings */
        switch ($Site->getAttribute('templateCologne.pageDesc')) {
            case 'enable':
                $pageShortDesc = $Site->getAttribute('templateCologne.pageDesc');
                break;

            case 'disable':
                $pageShortDesc = false;
        }

        // basket style
        $basketStyle = 'full';

        if ($Project->getConfig('templateCologne.settings.basketStyle')) {
            $basketStyle = $Project->getConfig('templateCologne.settings.basketStyle');
        }

        // basket open
        $basketAction = $Project->getConfig('templateCologne.settings.basketAction');

        if (!in_array($basketAction, ['openSmallBasket', 'openOrderProcessUrl', 'openOrderProcess'])) {
            $basketAction = 'openSmallBasket';
        }

        $settingsCSS = include 'settings.css.php';

        /**
         * Categories Menu
         */
        $showCategoryMenu = false;

        if (!$minimalDesign && $Project->getConfig('templateCologne.settings.showCategoryMenu')) {
            $showCategoryMenu = $Project->getConfig('templateCologne.settings.showCategoryMenu');
        }

        if ($showCategoryMenu) {
            $CategoriesMenu = new QUI\TemplateCologne\Controls\Menu\Categories([
                'showDescFor' => $Project->getConfig('templateCologne.settings.showCategoryShortFor'),
                'startId' => $Project->getConfig('templateCologne.settings.categoryStartId'),
                'showBasketButton' => $Project->getConfig('templateCologne.settings.showBasketButton')
            ]);

            $config['CategoriesMenu'] = QUI\ControlUtils::parse($CategoriesMenu);
        }

        /***
         * Mega menu settings
         */
        $homeLink = false;
        $homeLinkText = '';

        if ($Project->getConfig('templateCologne.settings.homeLink')) {
            $homeLink = $Project->getConfig('templateCologne.settings.homeLink');
        }

        if ($Project->getConfig('templateCologne.settings.homeLinkText')) {
            $text = json_decode($Project->getConfig('templateCologne.settings.homeLinkText'), true);

            if (isset($text[$lang]) && $text[$lang] !== '') {
                $homeLinkText = $text[$lang];
            }
        }

        /**
         * Menu appearance and smooth scroll
         */
        $showNavAfterScrollSetting = intval($Project->getConfig('templateCologne.settings.showNavAfterScroll'));
        $showNavAfterScroll = 0;
        $showMenuSmooth = false; // smooth animation
        // if true menu will be no longer fixed when user scrolls to menu initial position
        $setMenuPosBackOnInit = false;

        if ($showNavAfterScrollSetting && $showNavAfterScrollSetting > 0) {
            $showNavAfterScroll = $showNavAfterScrollSetting;
            $showMenuSmooth = true;
        }

        if ($Project->getConfig('templateCologne.settings.setMenuPosBackOnInit')) {
            $setMenuPosBackOnInit = true;
        }

        /* page custom class */
        $pageCustomClass = $Site->getAttribute('templateCologne.pageCustomClass');

        if ($pageCustomClass && $pageCustomClass !== '') {
            $pageCustomClass .= ' templateCologne__' . $pageCustomClass;
        }

        /**
         * Language and currency settings
         */
        switch ($Project->getConfig('templateCologne.settings.currencyLangSwitch.controlType')) {
            case 'onlyCurrency':
                $showTopbarLanguageSwitch = false;
                $showTopbarCurrencySwitch = true;
                break;

            case 'onlyLang':
                $showTopbarLanguageSwitch = true;
                $showTopbarCurrencySwitch = false;
                break;

            case 'disabled':
                $showTopbarLanguageSwitch = false;
                $showTopbarCurrencySwitch = false;
                break;

            case 'currencyAndLang':
            default:
                $showTopbarLanguageSwitch = true;
                $showTopbarCurrencySwitch = true;
                break;
        }


        // predefined footer
        $config += self::getPredefinedFooter($Project);

        $config['header'] = $header;
        $config['logoHeight'] = $logoHeight;
        $config['pageTitle'] = $pageTitle;
        $config['settings.pageShortDesc'] = $pageShortDesc;
        $config['showBreadcrumb'] = $showBreadcrumb;
        $config['minimalDesign'] = $minimalDesign;
        $config['showTopBar'] = $showTopBar;
        $config['showNav'] = $showNav;
        $config['showFooter'] = $showFooter;
        $config['settingsCSS'] = '<style data-no-cache="1">' . $settingsCSS . '</style>';
        $config['typeClass'] = 'type-' . str_replace(['/', ':'], '-', $Site->getAttribute('type'));
        $config['minimalDesignClass'] = $minimalDesign ? 'type-minimal-design' : '';
        $config['siteType'] = $siteType;
        $config['pageCustomClass'] = $pageCustomClass;
        $config['basketStyle'] = $basketStyle;
        $config['basketAction'] = $basketAction;
        $config['showCategoryMenu'] = $showCategoryMenu;
        $config['homeLink'] = $homeLink;
        $config['homeLinkText'] = $homeLinkText;
        $config['useSlideOutMenu'] = true; // for now is always true because quiqqer use currently only SlideOut nav
        $config['showNavAfterScroll'] = $showNavAfterScroll;
        $config['showMenuSmooth'] = $showMenuSmooth;
        $config['setMenuPosBackOnInit'] = $setMenuPosBackOnInit;
        $config['showTopbarLanguageSwitch'] = $showTopbarLanguageSwitch;
        $config['showTopbarCurrencySwitch'] = $showTopbarCurrencySwitch;

        // set cache
        QUI\Cache\Manager::set(
            'quiqqer/templateCologne/' . $cacheName,
            $config
        );

        return $config;
    }

    /**
     * Returns data for predefined footer if enabled.
     *
     * @param Project $Project
     *
     * @return array - data for predefined footer
     * @throws Exception
     */
    private static function getPredefinedFooter(Project $Project): array
    {
        $lang = $Project->getLang();

        /** Predefined footer: short text */
        $shortText = [];

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.shortText')) {
            $titles = json_decode(
                $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.shortText.title'
                ),
                true
            );

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $shortText['title'] = $title;
        }

        /** Predefined footer: url list */
        $urlList = [];

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.urlList')) {
            $titles = json_decode(
                $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.urlList.title'
                ),
                true
            );

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $siteIds = $Project->getConfig('templateCologne.settings.predefinedFooter.urlList.sites');
            $sites = [];

            if ($siteIds) {
                $sites = QUI\Projects\Site\Utils::getSitesByInputList($Project, $siteIds, [
                    'where' => [
                        'active' => 1
                    ],
                    'limit' => 10,
                    'order' => $Project->getConfig('templateCologne.settings.predefinedFooter.urlList.sites.order')
                ]);
            }

            $sitesData = [];

            foreach ($sites as $Site) {
                $sitesData[] = [
                    'title' => $Site->getAttribute('title'),
                    'url' => $Site->getUrlRewritten()
                ];
            }

            $urlList['title'] = $title;
            $urlList['sites'] = $sitesData;
            $urlList['productSearch'] = false;
            $urlList['legalNotes'] = false;
            $urlList['privacyPolicy'] = false;
            $urlList['generalTermsAndConditions'] = false;

            if ($Project->getConfig('templateCologne.settings.predefinedFooter.urlList.showStandardSites')) {
                /** productSearch */
                $productSearch = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type' => 'IN',
                            'value' => 'quiqqer/products:types/search'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($productSearch)) {
                    $urlList['productSearch'] = [
                        'title' => $productSearch[0]->getAttribute('title'),
                        'url' => $productSearch[0]->getUrlRewritten()
                    ];
                }

                /** legal notes (Impressum) */
                $legalNotes = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type' => 'IN',
                            'value' => 'quiqqer/sitetypes:types/legalnotes'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($legalNotes)) {
                    $urlList['legalNotes'] = [
                        'title' => $legalNotes[0]->getAttribute('title'),
                        'url' => $legalNotes[0]->getUrlRewritten()
                    ];
                }

                /** privacy policy (Datenschutzerklärung) */
                $privacyPolicy = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type' => 'IN',
                            'value' => 'quiqqer/sitetypes:types/privacypolicy'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($privacyPolicy)) {
                    $urlList['privacyPolicy'] = [
                        'title' => $privacyPolicy[0]->getAttribute('title'),
                        'url' => $privacyPolicy[0]->getUrlRewritten()
                    ];
                }

                /** general terms and conditinos (AGB) */
                $generalTermsAndConditions = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type' => 'IN',
                            'value' => 'quiqqer/sitetypes:types/generalTermsAndConditions'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($generalTermsAndConditions)) {
                    $urlList['generalTermsAndConditions'] = [
                        'title' => $generalTermsAndConditions[0]->getAttribute('title'),
                        'url' => $generalTermsAndConditions[0]->getUrlRewritten()
                    ];
                }
            }
        }

        /** Featured products */
        $featuredProducts = [];

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.featuredProducts')) {
            $FeaturedProduct = new QUI\ProductBricks\Controls\FeaturedProducts([
                'featured1.categoryId' => $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.featuredProducts.category'
                )
            ]);

            $featuredProducts['controlParsed'] = QUI\ControlUtils::parse($FeaturedProduct);

            $titles = json_decode(
                $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.featuredProducts.title'
                ),
                true
            );

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $featuredProducts['title'] = $title;
        }

        /** Predefined footer: Payments Control */
        $paymentsData = [];

        if (
            $Project->getConfig('templateCologne.settings.predefinedFooter.payments')
            && class_exists('\QUI\ERP\Accounting\Payments\Payments')
        ) {
            $PaymentsControl = new Payments([
                'template' => $Project->getConfig('templateCologne.settings.predefinedFooter.payments.layout')
            ]);

            $paymentsData['controlParsed'] = QUI\ControlUtils::parse($PaymentsControl);

            $titles = json_decode(
                $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.payments.title'
                ),
                true
            );

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $paymentsData['title'] = $title;
        }

        return [
            'shortText' => $shortText,
            'urlList' => $urlList,
            'featuredProducts' => $featuredProducts,
            'paymentsData' => $paymentsData
        ];
    }

    /**
     * Get FrontendView of ShippingTime field
     *
     * requires quiqqer/shipping to be installed
     *
     * @param int $productId
     *
     * @return false|QUI\ERP\Products\Field\View
     */
    public static function getShippingTimeFrontendView(int $productId): bool | QUI\ERP\Products\Field\View
    {
        try {
            $Product = QUI\ERP\Products\Handler\Products::getProduct($productId);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        if (class_exists('QUI\ERP\StockManagement\StockManager')) {
            return StockManager::getShippingTimeFrontendViewByProduct($Product);
        }

        if (!QUI::getPackageManager()->isInstalled('quiqqer/shipping')) {
            return false;
        }

        if (!class_exists('QUI\ERP\Shipping\Shipping')) {
            return false;
        }

        $reflection = new ReflectionClass(Shipping::class);

        if (!$reflection->hasConstant('PRODUCT_FIELD_SHIPPING_TIME')) {
            return false;
        }

        try {
            $ShippingField = $Product->getField(Shipping::PRODUCT_FIELD_SHIPPING_TIME);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        return $ShippingField->getFrontendView();
    }

    /**
     * Get FrontendView of DeliveryTime field
     *
     * requires quiqqer/stock-management to be installed
     *
     * @param int $productId
     *
     * @return false|QUI\ERP\Products\Field\View
     */
    public static function getStockFrontendView(int $productId)
    {
        try {
            $Project = QUI::getRewrite()->getProject();
            $showStock = $Project->getConfig('templateCologne.settings.showStock');

            if (empty($showStock)) {
                return false;
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        if (!QUI::getPackageManager()->isInstalled('quiqqer/stock-management')) {
            return false;
        }

        if (!class_exists('QUI\ERP\StockManagement\StockManager')) {
            return false;
        }

        try {
            $Product = QUI\ERP\Products\Handler\Products::getProduct($productId);
            $StockField = $Product->getField(StockManager::PRODUCT_FIELD_STOCK);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        $StockView = $StockField->getFrontendView();

        if (method_exists($StockView, 'setProduct')) {
            $StockView->setProduct($Product);
        }

        return $StockView;
    }

    /**
     * Add a suffix to brick css class(es)
     *
     * @param array $classes
     *
     * @return string
     */
    public static function convertBrickCSSClass(array $classes): string
    {
        if (count($classes) < 1) {
            return '';
        }

        $text = '';

        foreach ($classes as $classString) {
            $text .= ' brick-container__' . $classString;
        }

        return $text;
    }

    /**
     * Get template setting for given string.
     * By passing setting name you can omit template prefix setting name ("templateCologne.settings.")
     *
     * Usage:
     *   QUI\TemplateCologne\Utils::getSettings('homeLink');
     *   or
     *   QUI\TemplateCologne\Utils::getSettings('templateCologne.settings.homeLink');
     *
     * @param string $settingName
     * @return bool|array|int|string
     */
    public static function getSetting(string $settingName): bool | array | int | string
    {
        if (empty($settingName)) {
            return false;
        }

        $a = strpos($settingName, 'templateCologne.settings.');
        if (!str_contains($settingName, 'templateCologne.settings.')) {
            $settingName = 'templateCologne.settings.' . $settingName;
        }

        try {
            $Project = QUI::getRewrite()->getProject();

            return $Project->getConfig($settingName);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return '';
        }
    }
}
