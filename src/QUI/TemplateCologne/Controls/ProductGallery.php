<?php

/**
 * This file contains QUI\TemplateCologne\Controls\ProductGallery
 */

namespace QUI\TemplateCologne\Controls\ProductGallery;

use DusanKasan\Knapsack\Collection;
use QUI;
use QUI\ERP\Products\Handler\Fields;

//use QUI\ERP\Watchlist\Controls\ButtonAdd as WatchlistButton;
//use QUI\ERP\Watchlist\Controls\ButtonPurchase as PurchaseButton;

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
            'Product'  => false,
//            'data-qui' => 'package/quiqqer/products/bin/controls/frontend/products/Product'
        ]);

        $this->addCSSFile(dirname(__FILE__).'/ProductGallery.css');

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
        $Gallery = new QUI\Gallery\Controls\ImageSlider();

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

        $Gallery->setAttribute('height', '400px');

        $Engine->assign([
            'Gallery'              => $Gallery,
        ]);

        return $Engine->fetch(dirname(__FILE__).'/ProductGallery.html');
    }
}
