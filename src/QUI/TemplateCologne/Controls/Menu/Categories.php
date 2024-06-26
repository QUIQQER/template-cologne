<?php

/**
 * This file contains QUI\TemplateCologne\Controls\Menu\Categories
 *
 * Creates a "slide out" menu with product categories.
 *
 * @author www.pcsg.de (Michael Danielczok)
 */

namespace QUI\TemplateCologne\Controls\Menu;

use Exception;
use QUI;
use QUI\Menu\EventHandler;
use QUI\Projects\Site\Utils;

use function dirname;
use function md5;
use function serialize;

/**
 * Class Categories
 */
class Categories extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'class' => 'quiqqer-categories-menu',
            'startId' => 1, // site id or site link where menu starts by. 1 is start page (first project page)
            'template' => dirname(__FILE__) . '/Categories.html', // nav wrapper
            'menuFile' => dirname(__FILE__) . '/Categories.Menu.html', // contains children (sites),
            'jsControl' => 'package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories',
            'showDescFor' => 'all', // Show category description: all / firstLevel / none
            'showBasketButton' => false
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/Categories.css');

        parent::__construct($attributes);
    }

    /**
     * @return string
     * @throws QUI\Exception|Exception
     */
    public function getBody(): string
    {
        $Engine = QUI::getTemplateManager()->getEngine();
        $Project = $this->getProject();


        // start
        try {
            $startId = $this->getAttribute('startId');

            if (Utils::isSiteLink($startId)) {
                $Site = Utils::getSiteByLink($startId);
            } else {
                $Site = $Project->get((int)$startId);
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::addWarning($Exception->getMessage());

            return '';
        }

        $cache = EventHandler::menuCacheName() . '/megaMenu/';

        $cache .= md5(
            $this->getSite()->getCachePath() .
            serialize($this->getAttributes())
        );

        try {
            return QUI\Cache\Manager::get($cache);
        } catch (QUI\Exception) {
        }

        $showBasketButton = $this->getAttribute('showBasketButton');

        if (!QUI::getPackageManager()->isInstalled('quiqqer/order')) {
            $showBasketButton = false;
        }

        $Engine->assign([
            'menuFile' => $this->getAttribute('menuFile'),
            'this' => $this,
            'showDescFor' => $this->getAttribute('showDescFor'),
            'showBasketButton' => $showBasketButton,
            'Site' => $Site,
            'Project' => $Project
        ]);

        $result = $Engine->fetch($this->getAttribute('template'));

        QUI\Cache\Manager::set($cache, $result);

        return $result;
    }

    /**
     * Return the current site
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
}
