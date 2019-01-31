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
            'class'      => 'lang-currency-switch',
            'data-qui'   => 'package/quiqqer/template-cologne/bin/javascript/controls/LangCurrencySwitch',
            'flagFolder' => URL_BIN_DIR . '16x16/flags/',
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/LangCurrencySwitch.css');

        parent::__construct($attributes);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \QUI\Control::create()
     *
     * @throws QUI\Exception
     */
    public function getBody()
    {
        $Engine  = QUI::getTemplateManager()->getEngine();
        $Site    = $this->getSite();
        $Project = $Site->getProject();

        if (!$Site) {
            return '';
        }

        $flagFolder = $this->getAttribute('flagFolder');

        if (!is_dir($flagFolder)) {
            $flagFolder = URL_BIN_DIR . '16x16/flags/';
        }

        $this->setJavaScriptControlOption('flag-folder', $flagFolder);

        $Engine->assign([
            'this'            => $this,
            'projectLang'     => $Project->getLang(),
            'DefaultCurrency' => QUI\ERP\Currency\Handler::getDefaultCurrency(),
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
