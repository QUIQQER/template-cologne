<?php
/**
 * This file contains \QUI\TemplateCologne\EventHandler
 */

namespace QUI\TemplateCologne;

use QUI;

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
    public static function onProjectConfigSave()
    {
        try {
            QUI\Cache\Manager::clear('quiqqer/templateCologne');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }
    }

    /**
     * Clear system cache on site save
     *
     * @return void
     */
    public static function onSiteSave()
    {
        try {
            QUI\Cache\Manager::clear('quiqqer/templateCologne');
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }
    }

    /**
     * @param \Quiqqer\Engine\Collector $Collector
     * @param QUI\ERP\Products\Product\ViewFrontend $Product
     *
     * @throws QUI\Exception
     */
    public static function onQuiqqerProductsProductButtonsEnd(\Quiqqer\Engine\Collector $Collector, \QUI\ERP\Products\Product\ViewFrontend $Product)
    {
        // setting
        $Project = QUI::getRewrite()->getProject();

        if (!(int)$Project->getConfig('templateCologne.settings.showBuyNowButton')) {
            return;
        }

        $text     = QUI::getLocale()->get('quiqqer/template-cologne', 'control.product.buy.know.button');
        $disabled = 0;

        if (!$Product->getMaximumQuantity()) {
            $disabled = 1;
        }

        $Collector->append(
            '<div class="product-data-actionButtons-buyNow" data-qui-options-disabled="'.$disabled.'">
                <div class="product-data-actionButtons-buyNow-placeholder"></div>
                <button disabled data-qui="package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton"
                        data-qui-options-disabled="'.$disabled.'">
                    <span class="add-to-basket-text">'.$text.'</span>
                </button>
            </div>'
        );
    }
}
