<?php

/**
 * This file contains QUI\TemplateCologne\Controls\SimpleUserInfo
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

/**
 * Class ProductGallery
 */
class SimpleUserInfo extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'User' => null
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/SimpleUserInfo.css');

        parent::__construct($attributes);
    }

    /**
     * (non-PHPdoc)
     *
     * @throws QUI\Exception
     * @see \QUI\Control::create()
     */
    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $User = $this->getUser();

        $avatarUrl = URL_OPT_DIR . 'quiqqer/template-cologne/bin/images/avatar-placeholder.svg';

        if (QUI\TemplateCologne\Utils::getAvatar($User)) {
            $avatarUrl = QUI\TemplateCologne\Utils::getAvatar($User)->getSizeCacheUrl();
        }

        $Engine->assign([
            'name' => $User->getName(),
            'registrationDay' => $User->getAttribute('regdate'),
            'avatarUrl' => $avatarUrl,
            'ordersNumber' => $this->getOrdersNumber($User)
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/SimpleUserInfo.html');
    }

    /**
     * Return the User
     *
     * @return QUI\Interfaces\Users\User
     * @throws QUI\FrontendUsers\Exception
     */
    public function getUser()
    {
        $User = $this->getAttribute('User');

        if ($User === false) {
            return QUI::getUserBySession();
        }

        if ($User instanceof QUI\Interfaces\Users\User) {
            return $User;
        }

        throw new QUI\FrontendUsers\Exception([
            'quiqqer/frontend-users',
            'exception.ser.was.not.net'
        ]);
    }

    /**
     * Return the current site
     *
     * @return QUI\Projects\Site
     * @throws QUI\Exception
     */
    public function getSite()
    {
        if ($this->getAttribute('Site')) {
            return $this->getAttribute('Site');
        }

        return QUI::getRewrite()->getSite();
    }

    /**
     * Get number of orders
     *
     * @param $User QUI\Interfaces\Users\User
     *
     * @return int
     */
    public function getOrdersNumber($User)
    {
        $Orders = QUI\ERP\Order\Handler::getInstance();

        return $Orders->countOrdersByUser($User);
    }
}
