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
            'class'    => 'loginAndRegister'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/LoginAndRegister.css');

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
        $Engine = QUI::getTemplateManager()->getEngine();

        $Login = new QUI\FrontendUsers\Controls\Login([
            'header'        => false
        ]);

        $Engine->assign([
            'Login' => $Login
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/LoginAndRegister.html');
    }
}
