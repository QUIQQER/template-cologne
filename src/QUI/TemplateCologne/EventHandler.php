<?php

/**
 * This file contains \QUI\TemplateCologne\EventHandler
 */

namespace QUI\TemplateCologne;

use QUI;
use Smarty;
use QUI\ERP\Products\Product\ViewFrontend;
use QUI\Smarty\Collector;

/**
 * Event Class
 *
 * @author www.pcsg.de (Michael Danielczok)
 */
class EventHandler
{
    /**
     * Clear system cache on project save
     *
     * @return void
     */
    public static function onProjectConfigSave(): void
    {
        QUI\Cache\Manager::clear('quiqqer/templateCologne');
    }

    /**
     * Clear system cache on site save
     *
     * @return void
     */
    public static function onSiteSave(): void
    {
        QUI\Cache\Manager::clear('quiqqer/templateCologne');
    }

    /**
     * @param Collector $Collector
     * @param ViewFrontend $Product
     *
     * @throws QUI\Exception
     */
    public static function onQuiqqerProductsProductButtonsEnd(
        QUI\Smarty\Collector $Collector,
        ViewFrontend $Product
    ): void {
        // setting
        $Project = QUI::getRewrite()->getProject();

        if (!(int)$Project->getConfig('templateCologne.settings.showBuyNowButton')) {
            return;
        }

        $text = QUI::getLocale()->get('quiqqer/template-cologne', 'control.product.buy.know.button');
        $disabled = 0;

        if (!$Product->getMaximumQuantity()) {
            $disabled = 1;
        }

        $Collector->append(
            '<div class="product-data-actionButtons-buyNow" data-qui-options-disabled="' . $disabled . '">
                <div class="product-data-actionButtons-buyNow-placeholder"></div>
                <button disabled data-qui="package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton"
                        data-qui-options-disabled="' . $disabled . '">
                    <span class="add-to-basket-text">' . $text . '</span>
                </button>
            </div>'
        );
    }

    /**
     * Event: on smarty init
     *
     * @param Smarty $Smarty
     * @return void
     */
    public static function onSmartyInit(Smarty $Smarty): void
    {
        $Smarty->registerClass('QUI\TemplateCologne\Utils', '\QUI\TemplateCologne\Utils');
        $Smarty->registerClass('QUI\Bricks\Manager', '\QUI\Bricks\Manager');
        $Smarty->registerClass('QUI\ERP\Products\Utils\Products', '\QUI\ERP\Products\Utils\Products');
    }
}
