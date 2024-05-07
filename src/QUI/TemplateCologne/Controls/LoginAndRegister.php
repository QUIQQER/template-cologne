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
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'nodeName' => 'section',
            'class' => 'loginAndRegister'
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/LoginAndRegister.css');

        parent::__construct($attributes);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $Login = new QUI\FrontendUsers\Controls\Login([
            'header' => true,
            'passwordReset' => true
        ]);

        $Registration = new QUI\FrontendUsers\Controls\RegistrationSignUp([
            'content' => false
        ]);

        $Engine->assign([
            'Login' => $Login,
            'Registration' => $Registration
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/LoginAndRegister.html');
    }
}
