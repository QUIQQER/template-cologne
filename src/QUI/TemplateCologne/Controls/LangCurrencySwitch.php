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
            'class'          => 'lang-currency-switch',
            'flagFolderPath' => URL_BIN_DIR . '16x16/flags/'
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

        QUI\System\Log::writeRecursive($Project->getLanguages());

        $Engine->assign([
            'this'            => $this,
            'Site' => $Site,
            'projectLang'     => $Project->getLang(),
            'DefaultCurrency' => QUI\ERP\Currency\Handler::getDefaultCurrency(),
            'langs'           => $Project->getLanguages()
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
