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
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'class'               => 'lang-currency-switch',
            'data-qui'            => 'package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch',
            'userRelatedCurrency' => 1, // 1 / 0 -> is user allowed to change currency?
            'flagFolder'          => URL_BIN_DIR . '16x16/flags/',
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
    public function getBody()
    {
        $Engine  = QUI::getTemplateManager()->getEngine();
        $Site    = $this->getSite();
        $Project = $Site->getProject();

        // is user allowed to change currency?
        try {
            $Package = QUI::getPackage('quiqqer/erp');
            $Config  = $Package->getConfig();

            if (!$Config->getValue('general', 'userRelatedCurrency')) {
                $this->setJavaScriptControlOption('userrelatedcurrency', '0');
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }

        if (!$Site) {
            return '';
        }

        $flagFolder = $this->getAttribute('flagFolder');

        if (!is_dir($flagFolder)) {
            $flagFolder = URL_BIN_DIR . '16x16/flags/';
        }

        $this->setJavaScriptControlOption('flag-folder', $flagFolder);

        $Currency = QUI\ERP\Currency\Handler::getDefaultCurrency();

        if (QUI\ERP\Currency\Handler::getUserCurrency()) {
            $Currency = QUI\ERP\Currency\Handler::getUserCurrency();
        }

        $Engine->assign([
            'this'            => $this,
            'projectLang'     => $Project->getLang(),
            'DefaultCurrency' => $Currency,
            'flagFolderPath'  => $flagFolder
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/LangCurrencySwitch.html');
    }

    /**
     * Return the Project
     *
     * @return QUI\Projects\Site
     */
    protected function getSite()
    {
        if ($this->getAttribute('Site')) {
            return $this->getAttribute('Site');
        }

        return QUI::getRewrite()->getSite();
    }
}
