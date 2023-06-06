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
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'class'               => 'currency-switch',
//            'data-qui'            => 'package/quiqqer/template-cologne/bin/javascript/controls/CurrencySwitch',
            'userRelatedCurrency' => 1, // 1 / 0 -> is user allowed to change currency?
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/CurrencySwitch.css');

        parent::__construct($attributes);
    }

    /**
     * (non-PHPdoc)
     *
     * @throws QUI\Exception
     * @see \QUI\Control::create()
     *
     */
    public function getBody()
    {
        $Engine       = QUI::getTemplateManager()->getEngine();
        $enableChange = false;

        // is user allowed to change currency?
        $currencySwitch = false;
        $userrelatedcurrency = 0;

        if ($this->isCurrencySwitchAllowed()) {
            try {
                $Package = QUI::getPackage('quiqqer/erp');
                $Config  = $Package->getConfig();

                if ($Config->getValue('general', 'userRelatedCurrency')) {
                    $userrelatedcurrency = 1;
                    $currencySwitch = true;
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }
        $this->setJavaScriptControlOption('userrelatedcurrency', $userrelatedcurrency);


        if ($currencySwitch) {
            $enableChange = true;
        }

        $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

        if (QUI\ERP\Currency\Handler::getUserCurrency()) {
            $Currency = QUI\ERP\Currency\Handler::getUserCurrency();
        }

        $Engine->assign([
            'this'            => $this,
            'currencySwitch'  => $currencySwitch,
            'DefaultCurrency' => $Currency,
            'enableChange'    => $enableChange
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/CurrencySwitch.html');
    }

    /**
     * Is currency switch allowed? Setting has over currencies number.
     *
     * @return bool
     * @throws QUI\Exception
     */
    protected function isCurrencySwitchAllowed()
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
