<?php

/**
 * This file contains QUI\TemplateCologne\Controls\Menu\Categories
 *
 * Creates an "slide out" menu with product categories.
 *
 * @author www.pcsg.de (Michael Danielczok)
 */

namespace QUI\TemplateCologne\Controls\Menu;

use QUI;
use QUI\Projects\Site\Utils;

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
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'class'       => 'quiqqer-categories-menu',
            'startId'     => 1, // site id or site link where menu starts by. 1 is start page (first project page)
            'template'    => dirname(__FILE__) . '/Categories.html', // nav wrapper
            'menuFile'    => dirname(__FILE__) . '/Categories.Menu.html', // contains children (sites),
            'data-qui'    => 'package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories',
            'showDescFor' => 'all' // Show category description: all / firstLevel / none
        ]);

        $this->addCSSFile(dirname(__FILE__) . '/Categories.css');

        parent::__construct($attributes);
    }

    /**
     * @return string
     * @throws QUI\Exception
     */
    public function getBody()
    {
        $Engine  = QUI::getTemplateManager()->getEngine();
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


        $Engine->assign([
            'menuFile'    => $this->getAttribute('menuFile'),
            'this'        => $this,
            'showDescFor' => $this->getAttribute('showDescFor'),
            'Site'        => $Site,
            'Project'     => $Project
        ]);

        return $Engine->fetch($this->getAttribute('template'));
    }

    /**
     * Return the current site
     *
     * @return mixed|QUI\Projects\Site
     */
    protected function getSite()
    {
        if ($this->getAttribute('Site')) {
            return $this->getAttribute('Site');
        }

        return QUI::getRewrite()->getSite();
    }
}
