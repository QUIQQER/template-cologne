<?php

/**
 * This file contains QUI\TemplateCologne\Utils
 */

namespace QUI\TemplateCologne;

use QUI;
use QUI\ERP\Shipping\Shipping;
use QUI\ERP\StockManagement\StockManager;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Get user avatar. If no avatar available return false.
     *
     * @param $User QUI\Interfaces\Users\User
     * @return QUI\Projects\Media\Image|false
     * @throws QUI\Exception
     *
     */
    public static function getAvatar($User)
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
            if ($Entry instanceof QUI\Interfaces\Projects\Media\File) {
                return $Entry;
            }
        }

        $avatar = $User->getAttribute('avatar');

        if (!QUI\Projects\Media\Utils::isMediaUrl($avatar)) {
            return false;
        }

        try {
            return QUI\Projects\Media\Utils::getImageByUrl($avatar);
        } catch (QUI\Exception $Exception) {
        }

        return false;
    }

    /**
     * Returns config. If a cache exists, it will be returned.
     *
     * @param $params
     * @return array|bool|object|string
     * @throws QUI\Exception
     */
    public static function getConfig($params)
    {
        /** @var $Site \QUI\Projects\Site */
        $Site = $params['Site'];

        /* @var $Project QUI\Projects\Project */
        $Project = $params['Project'];

        $cacheName = md5($params['Site']->getId() . $Project->getName() . $Project->getLang());

        try {
            return QUI\Cache\Manager::get(
                'quiqqer/templateCologne/' . $cacheName
            );
        } catch (QUI\Exception $Exception) {
        }

        $config = [];

        $lang = $Project->getLang();

        /**
         * no header?
         * no breadcrumb?
         * Body Class
         *
         * own site type
         */
        $header         = 'hide';
        $pageTitle      = 'breadcrumb'; // where to show page title: in header, in breadcrumb or both?
        $showBreadcrumb = false;
        $siteType       = 'no-sidebar';

        switch ($Site->getAttribute('layout')) {
            case 'layout/startPage':
                $header         = $Project->getConfig('templateCologne.settings.headerStartPage');
                $pageTitle      = $Project->getConfig('templateCologne.settings.pageTitleStartPage');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbStartPage');
                $siteType       = 'layout-start-page';
                break;

            case 'layout/noSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerNoSidebar');
                $pageTitle      = $Project->getConfig('templateCologne.settings.pageTitleNoSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebar');
                $siteType       = 'layout-no-sidebar';
                break;

            case 'layout/noSidebarThin':
                $header         = $Project->getConfig('templateCologne.settings.headerNoSidebarThin');
                $pageTitle      = $Project->getConfig('templateCologne.settings.pageTitleNoSidebarThin');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebarThin');
                $siteType       = 'layout-no-sidebar';
                break;

            case 'layout/rightSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerRightSidebar');
                $pageTitle      = $Project->getConfig('templateCologne.settings.pageTitleRightSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbRightSidebar');
                $siteType       = 'layout-right-sidebar';
                break;

            case 'layout/leftSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerLeftSidebar');
                $pageTitle      = $Project->getConfig('templateCologne.settings.pageTitleLeftSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbLeftSidebar');
                $siteType       = 'layout-left-sidebar';
                break;
        }

        if ($Site->getAttribute('type') === 'quiqqer/order:types/orderingProcess' ||
            $Site->getAttribute('type') === 'quiqqer/order:types/shoppingCart') {
            $showBreadcrumb = false;
        }

        /* site own show header */
        switch ($Site->getAttribute('templateCologne.header')) {
            case 'afterNav':
            case 'beforeContent':
            case 'hide':
                $header = $Site->getAttribute('templateCologne.header');
        }

        // basket style
        $basketStyle = 'full';

        if ($Project->getConfig('templateCologne.settings.basketStyle')) {
            $basketStyle = $Project->getConfig('templateCologne.settings.basketStyle');
        }

        // basket open
        $basketOpen = 2;

        switch ($Project->getConfig('templateCologne.settings.basketOpen')) {
            case '0':
            case '1':
            case '2':
                $basketOpen = $Project->getConfig('templateCologne.settings.basketOpen');
        }

        $settingsCSS = include 'settings.css.php';

        /**
         * Categories Menu
         */
        $showCategoryMenu = false;

        if ($Project->getConfig('templateCologne.settings.showCategoryMenu')) {
            $showCategoryMenu = $Project->getConfig('templateCologne.settings.showCategoryMenu');
        }

        if ($showCategoryMenu) {
            $CategoriesMenu = new QUI\TemplateCologne\Controls\Menu\Categories([
                'showDescFor'      => $Project->getConfig('templateCologne.settings.showCategoryShortFor'),
                'startId'          => $Project->getConfig('templateCologne.settings.categoryStartId'),
                'showBasketButton' => $Project->getConfig('templateCologne.settings.showBasketButton')
            ]);

            $config['CategoriesMenu'] = QUI\ControlUtils::parse($CategoriesMenu);
        }

        /***
         * Mega menu settings
         */
        $homeLink     = false;
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

        // predefined footer
        $config += self::getPredefinedFooter($Project);

        $config['header']           = $header;
        $config['pageTitle']        = $pageTitle;
        $config['showBreadcrumb']   = $showBreadcrumb;
        $config['settingsCSS']      = '<style>' . $settingsCSS . '</style>';
        $config['typeClass']        = 'type-' . str_replace(['/', ':'], '-', $Site->getAttribute('type'));
        $config['siteType']         = $siteType;
        $config['basketStyle']      = $basketStyle;
        $config['basketOpen']       = $basketOpen;
        $config['showCategoryMenu'] = $showCategoryMenu;
        $config['homeLink']         = $homeLink;
        $config['homeLinkText']     = $homeLinkText;

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
     * @param \QUI\Projects\Project $Project
     * @return array - data for predefined footer
     */
    private static function getPredefinedFooter($Project)
    {
        $lang = $Project->getLang();

        /** Predefined footer: short text */
        $shortText = false;

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.shortText')) {
            $shortText = [];

            $titles = json_decode($Project->getConfig(
                'templateCologne.settings.predefinedFooter.shortText.title'
            ), true);

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $shortText['title'] = $title;
        }

        /** Predefined footer: url list */
        $urlList = false;

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.urlList')) {
            $urlList = [];

            $titles = json_decode($Project->getConfig(
                'templateCologne.settings.predefinedFooter.urlList.title'
            ), true);

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $siteIds = $Project->getConfig('templateCologne.settings.predefinedFooter.urlList.sites');
            $sites   = [];

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
                    'url'   => $Site->getUrlRewritten()
                ];
            }

            $urlList['title']                     = $title;
            $urlList['sites']                     = $sitesData;
            $urlList['productSearch']             = false;
            $urlList['legalNotes']                = false;
            $urlList['privacyPolicy']             = false;
            $urlList['generalTermsAndConditions'] = false;

            if ($Project->getConfig('templateCologne.settings.predefinedFooter.urlList.showStandardSites')) {
                /** productSearch */
                $productSearch = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type'  => 'IN',
                            'value' => 'quiqqer/products:types/search'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($productSearch)) {
                    $urlList['productSearch'] = [
                        'title' => $productSearch[0]->getAttribute('title'),
                        'url'   => $productSearch[0]->getUrlRewritten()
                    ];
                }

                /** legal notes (Impressum) */
                $legalNotes = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type'  => 'IN',
                            'value' => 'quiqqer/sitetypes:types/legalnotes'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($legalNotes)) {
                    $urlList['legalNotes'] = [
                        'title' => $legalNotes[0]->getAttribute('title'),
                        'url'   => $legalNotes[0]->getUrlRewritten()
                    ];
                }

                /** privacy policy (Datenschutzerklärung) */
                $privacyPolicy = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type'  => 'IN',
                            'value' => 'quiqqer/sitetypes:types/privacypolicy'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($privacyPolicy)) {
                    $urlList['privacyPolicy'] = [
                        'title' => $privacyPolicy[0]->getAttribute('title'),
                        'url'   => $privacyPolicy[0]->getUrlRewritten()
                    ];
                }

                /** general terms and conditinos (AGB) */
                $generalTermsAndConditions = $Project->getSites([
                    'where' => [
                        'type' => [
                            'type'  => 'IN',
                            'value' => 'quiqqer/sitetypes:types/generalTermsAndConditions'
                        ]
                    ],
                    'limit' => 1
                ]);

                if (count($generalTermsAndConditions)) {
                    $urlList['generalTermsAndConditions'] = [
                        'title' => $generalTermsAndConditions[0]->getAttribute('title'),
                        'url'   => $generalTermsAndConditions[0]->getUrlRewritten()
                    ];
                }
            }
        }

        /** Featured products */
        $featuredProducts = false;

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.featuredProducts')) {
            $FeaturedProduct = new QUI\ProductBricks\Controls\FeaturedProducts([
                'featured1.categoryId' => $Project->getConfig(
                    'templateCologne.settings.predefinedFooter.featuredProducts.category'
                )
            ]);

            $featuredProducts['controlParsed'] = QUI\ControlUtils::parse($FeaturedProduct);

            $titles = json_decode($Project->getConfig(
                'templateCologne.settings.predefinedFooter.featuredProducts.title'
            ), true);

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $featuredProducts['title'] = $title;
        }

        /** Predefined footer: Payments Control */
        $paymentsData = false;

        if ($Project->getConfig('templateCologne.settings.predefinedFooter.payments') &&
            \class_exists('\QUI\ERP\Accounting\Payments\Payments')) {
            $PaymentsControl = new \QUI\TemplateCologne\Controls\Payments([
                'template' => $Project->getConfig('templateCologne.settings.predefinedFooter.payments.layout')
            ]);

            $paymentsData['controlParsed'] = QUI\ControlUtils::parse($PaymentsControl);

            $titles = json_decode($Project->getConfig(
                'templateCologne.settings.predefinedFooter.payments.title'
            ), true);

            $title = false;

            if (isset($titles[$lang])) {
                $title = $titles[$lang];
            }

            $paymentsData['title'] = $title;
        }

        return [
            'shortText'        => $shortText,
            'urlList'          => $urlList,
            'featuredProducts' => $featuredProducts,
            'paymentsData'     => $paymentsData
        ];
    }

    /**
     * Get FrontendView of ShippingTime field
     *
     * requires quiqqer/shipping to be installed
     *
     * @param int $productId
     * @return false|QUI\ERP\Products\Field\View
     */
    public static function getShippingTimeFrontendView(int $productId)
    {
        try {
            $Product = QUI\ERP\Products\Handler\Products::getProduct($productId);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        if (QUI::getPackageManager()->isInstalled('quiqqer/stock-management')) {
            return StockManager::getShippingTimeFrontendViewByProduct($Product);
        }

        if (!QUI::getPackageManager()->isInstalled('quiqqer/shipping')) {
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
     * @return false|QUI\ERP\Products\Field\View
     */
    public static function getStockFrontendView(int $productId)
    {
        try {
            $Project   = QUI::getRewrite()->getProject();
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

        try {
            $Product    = QUI\ERP\Products\Handler\Products::getProduct($productId);
            $StockField = $Product->getField(StockManager::PRODUCT_FIELD_STOCK);
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }

        /** @var QUI\ERP\StockManagement\Products\Fields\StockView $StockView */
        $StockView = $StockField->getFrontendView();

        if (\method_exists($StockView, 'setProduct')) {
            $StockView->setProduct($Product);
        }

        return $StockView;
    }
}
