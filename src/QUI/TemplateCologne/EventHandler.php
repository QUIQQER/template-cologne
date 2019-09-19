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
     * Event : on smarty init
     * @param \Smarty $Smarty - \Smarty
     */
    public static function onSmartyInit($Smarty)
    {
        if (!isset($Smarty->registered_plugins['function']) ||
            !isset($Smarty->registered_plugins['function']['fetch'])
        ) {
            $Smarty->registerPlugin(
                "function",
                "fetch",
                "\\QUI\\TemplateCologne\\EventHandler::fetch"
            );
        }
    }

    /**
     * @param $params
     * @param $Smarty
     * @return string
     */
    public static function fetch($params, $Smarty)
    {
        $template = $params['template'];
        $path     = OPT_DIR.'quiqqer/template-cologne/';

        $Engine = QUI::getTemplateManager()->getEngine();
        $Engine->assign($params);

        return $Engine->fetch($path.$template);
    }

    /**
     * @param \Quiqqer\Engine\Collector $Collector
     */
    public static function onQuiqqerProductsProductButtonsEnd(\Quiqqer\Engine\Collector $Collector)
    {
        // setting
        $Project = QUI::getRewrite()->getProject();

        if (!(int)$Project->getConfig('templateCologne.settings.showBuyNowButton')) {
            return;
        }

        $text = QUI::getLocale()->get('quiqqer/template-cologne', 'control.product.buy.know.button');

        $Collector->append(
            '<div class="product-data-actionButtons-buyNow">
                <button disabled data-qui="package/quiqqer/template-cologne/bin/javascript/controls/BuyNowButton">
                    <span class="add-to-basket-text">'.$text.'</span>
                </button>
            </div>'
        );
    }
}
