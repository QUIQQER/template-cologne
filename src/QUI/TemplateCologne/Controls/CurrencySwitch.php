<?php

/**
 * This file contains QUI\TemplateCologne\Controls\CurrencySwitch
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

/**
 * Class LangCurrencySwitch
 */
class CurrencySwitch extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'class' => 'quiqqer-currency-switch',
            'userRelatedCurrency' => 1, // 1 / 0 -> is user allowed to change currency?
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/CurrencySwitch.css');

        parent::__construct($attributes);
    }

    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $this->setJavaScriptControlOption('buttonshowsign', 1);
        $this->setJavaScriptControlOption('dropdownshowsign', 1);
        $this->setJavaScriptControlOption('showarrow', 0);
        $this->setJavaScriptControlOption('showloader', 0);
        $this->setJavaScriptControlOption('dropdownposition', 'right');

        // is user allowed to change currency?
        if ($this->isCurrencySwitchAllowed()) {
            try {
                $Package = QUI::getPackage('quiqqer/erp');
                $Config = $Package->getConfig();

                if ($Config->getValue('general', 'userRelatedCurrency')) {
                    $this->setJavaScriptControl('package/quiqqer/currency/bin/controls/Switch');
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $Engine->assign([
            'this' => $this,
            'DefaultCurrency' => QUI\ERP\Currency\Handler::getRuntimeCurrency()
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/CurrencySwitch.html');
    }

    /**
     * Is currency switch allowed? Setting has over currencies number.
     *
     * @return bool
     */
    protected function isCurrencySwitchAllowed(): bool
    {
        if (!$this->getAttribute('userRelatedCurrency')) {
            return false;
        }

        $currencies = QUI\ERP\Currency\Handler::getAllowedCurrencies();

        if (count($currencies) > 1) {
            return true;
        }

        return false;
    }
}
