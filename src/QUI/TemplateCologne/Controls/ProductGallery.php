<?php

/**
 * This file contains QUI\TemplateCologne\Controls\ProductGallery
 */

namespace QUI\TemplateCologne\Controls;

use QUI;
use QUI\ERP\Products\Handler\Fields;

/**
 * Class ProductGallery
 */
class ProductGallery extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'Product' => false,
//            'data-qui' => 'package/quiqqer/products/bin/controls/frontend/products/Product'
        ]);

//        $this->addCSSFile(dirname(__FILE__).'/ProductGallery.css');

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
        /* @var $Product QUI\ERP\Products\Product\Product */
        $Engine  = QUI::getTemplateManager()->getEngine();
        $Product = $this->getAttribute('Product');
        $Gallery = new QUI\Gallery\Controls\Slider();

        // gallery
        $PlaceholderImage = $this->getProject()->getMedia()->getPlaceholderImage();

        if ($PlaceholderImage) {
            $Gallery->setAttribute(
                'placeholderimage',
                $PlaceholderImage->getSizeCacheUrl()
            );

            $Gallery->setAttribute('placeholdercolor', '#fff');
        }

        try {
            $Gallery->setAttribute('folderId', $Product->getFieldValue(Fields::FIELD_FOLDER));
        } catch (QUI\Exception $Exception) {
        }

        $height = '400px';
        if ($this->getAttribute('height')) {
            $height = $this->getAttribute('height');
        }

        $Gallery->setAttribute('height', $height);

        $Gallery->setAttribute('data-qui-options-show-controls-always', 0);
        $Gallery->setAttribute('data-qui-options-show-title-always', 0);
        $Gallery->setAttribute('data-qui-options-show-title', 0);
        $Gallery->setAttribute('data-qui-options-imagefit', 0);

        $Gallery->setAttribute('data-qui-options-preview', 1);
        $Gallery->setAttribute('data-qui-options-preview-outside', 1);
        $Gallery->setAttribute('data-qui-options-preview-background-color', '#fff');
        $Gallery->setAttribute('data-qui-options-preview-color', '#ddd');

        $Engine->assign([
            'Gallery' => $Gallery,
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/ProductGallery.html');
    }
}
