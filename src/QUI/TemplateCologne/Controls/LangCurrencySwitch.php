<?php

/**
 * This file contains QUI\TemplateCologne\Controls\LangCurrencySwitch
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

/**
 * Class LangCurrencySwitch
 */
class LangCurrencySwitch extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'class' => 'lang-currency-switch',
            'data-qui' => 'package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch',
            'userRelatedCurrency' => 1, // 1 / 0 -> is user allowed to change currency?
            'flagFolder' => URL_BIN_DIR . '16x16/flags/',
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/LangCurrencySwitch.css');

        parent::__construct($attributes);
    }

    /**
     * (non-PHPdoc)
     *
     * @throws QUI\Exception
     * @see \QUI\Control::create()
     *
     */
    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $Site = $this->getSite();
        $Project = $Site->getProject();
        $Locale = QUI::getLocale();
        $flagFolder = $this->getAttribute('flagFolder');
        $enableChange = false;

        // is user allowed to change currency?
        $currencySwitch = false;
        $this->setJavaScriptControlOption('userrelatedcurrency', '0');

        if ($this->isCurrencySwitchAllowed()) {
            try {
                $Package = QUI::getPackage('quiqqer/erp');
                $Config = $Package->getConfig();

                if ($Config->getValue('general', 'userRelatedCurrency')) {
                    $this->setJavaScriptControlOption('userrelatedcurrency', '1');
                    $currencySwitch = true;
                }
            } catch (QUI\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        $langSwitch = false;
        if (\count($Project->getLanguages()) > 1) {
            $langSwitch = true;
        }

        if ($currencySwitch || $langSwitch) {
            $this->setJavaScriptControlOption('flag-folder', $flagFolder);
            $enableChange = true;
        }

        $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

        if (QUI\ERP\Currency\Handler::getUserCurrency()) {
            $Currency = QUI\ERP\Currency\Handler::getUserCurrency();
        }

        if ($Locale->exists('quiqqer/core', 'language.' . $Project->getLang())) {
            $imgAltText = $Locale->get('quiqqer/core', 'language.' . $Project->getLang());
        } else {
            $imgAltText = $Locale->get('quiqqer/template-cologne', 'label.language');
        };

        $Engine->assign([
            'this' => $this,
            'projectLang' => $Project->getLang(),
            'currencySwitch' => $currencySwitch,
            'DefaultCurrency' => $Currency,
            'flagFolderPath' => $flagFolder,
            'imgAltText' => $imgAltText,
            'enableChange' => $enableChange
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/LangCurrencySwitch.html');
    }

    /**
     * Return the Project
     *
     * @return QUI\Interfaces\Projects\Site
     * @throws QUI\Exception
     */
    protected function getSite(): QUI\Interfaces\Projects\Site
    {
        if ($this->getAttribute('Site')) {
            return $this->getAttribute('Site');
        }

        return QUI::getRewrite()->getSite();
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
