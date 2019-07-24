<?php

/**
 * This file contains QUI\TemplateCologne\Controls\LoginAndRegister
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

/**
 * Class ProductGallery
 */
class LoginAndRegister extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'nodeName' => 'section',
            'class' => 'loginAndRegister'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/LoginAndRegister.css');

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
        $Engine = QUI::getTemplateManager()->getEngine();

        $LoginControl = new QUI\Users\Controls\Login();

        $Engine->assign([
            'LoginControl' => $LoginControl
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/LoginAndRegister.html');
    }
}
