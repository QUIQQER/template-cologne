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
            'template' => dirname(__FILE__) . '/Categories.html', // nav wrapper
            'menuFile' => dirname(__FILE__) . '/Categories.Menu.html', // contains children (sites),
            'data-qui' => 'package/quiqqer/template-cologne/bin/javascript/controls/Menu/Categories',

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
        $Engine = QUI::getTemplateManager()->getEngine();

        $Engine->assign([
            'menuFile' => $this->getAttribute('menuFile'),
            'this'     => $this,
            'Site'     => $this->getSite(),
            'Project'  => $this->getProject()
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
