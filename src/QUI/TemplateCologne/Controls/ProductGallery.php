<?php

/**
 * This file contains QUI\TemplateCologne\Controls\ProductGallery
 */

namespace QUI\TemplateCologne\Controls;

use QUI;
use QUI\ERP\Products\Handler\Fields;
use function is_a;
use function usort;

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
            'Product' => false
        ]);

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
        if (!$this->getAttribute('Product')) {
            return '';
        }

        $Engine  = QUI::getTemplateManager()->getEngine();
        $Product = $this->getAttribute('Product');
        $Gallery = new QUI\Gallery\Controls\Slider();

        if ($Product instanceof QUI\ERP\Products\Product\ViewFrontend) {
            $Product = $Product->getProduct();
        }

        $typeVariantParent = is_a($Product->getType(), QUI\ERP\Products\Product\Types\VariantParent::class, true);
        $typeVariantChild  = is_a($Product->getType(), QUI\ERP\Products\Product\Types\VariantChild::class, true);

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

        if ($typeVariantParent || $typeVariantChild) {
            $Gallery->setAttribute('folderId', false);

            foreach ($this->getVariantImages($Product) as $Image) {
                $Gallery->addImage($Image);
            }
        }

        $height = '400px';
        if ($this->getAttribute('height')) {
            $height = $this->getAttribute('height');
        }

        $Gallery->setAttribute('height', $height);

        $Gallery->setAttribute('data-qui-options-show-controls-always', 0);
        $Gallery->setAttribute('data-qui-options-show-title-always', 0);
        $Gallery->setAttribute('data-qui-options-show-title', 0);
        $Gallery->setAttribute('data-qui-options-imagefit', 1);

        $Gallery->setAttribute('data-qui-options-preview', 1);
        $Gallery->setAttribute('data-qui-options-preview-outside', 1);
        $Gallery->setAttribute('data-qui-options-preview-background-color', '#fff');
        $Gallery->setAttribute('data-qui-options-preview-color', '#ddd');

        $Engine->assign([
            'Gallery' => $Gallery,
        ]);

        return $Engine->fetch(dirname(__FILE__).'/ProductGallery.html');
    }

    /**
     * Get product images (for variant parents and children).
     * \QUI\ERP\Products\Controls\Products\Product::getVariantImages
     * By @peat
     *
     * @param QUI\ERP\Products\Product\Product $Product
     * @return QUI\Projects\Media\Image[]
     */
    protected function getVariantImages(QUI\ERP\Products\Product\Product $Product): array
    {
        $images = $Product->getImages();

        try {
            $MainImage    = $Product->getImage();
            $mainImageId  = $MainImage->getId();
            $hasMainImage = false;

            foreach ($images as $Image) {
                if ($Image->getId() === $MainImage->getId()) {
                    $hasMainImage = true;
                    break;
                }
            }

            if (!$hasMainImage) {
                $images[] = $MainImage;
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
            $mainImageId = false;
        }

        usort($images, function ($ImageA, $ImageB) use ($mainImageId) {
            /**
             * @var QUI\Projects\Media\Image $ImageA
             * @var QUI\Projects\Media\Image $ImageB
             */
            if ($ImageA->getId() === $mainImageId) {
                return -1;
            }

            if ($ImageB->getId() === $mainImageId) {
                return 1;
            }

            return 0;
        });

        return $images;
    }
}
