<?php

/**
 * This file contains QUI\TemplateCologne\Controls\SimpleUserInfo
 *
 * This control creates a list of payments that are configured in system.
 * You can show it a list or grid.
 *
 * @author Michael Danielczok (www.pcsg.de)
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

/**
 * Class Payments
 */
class Payments extends QUI\Control
{
    /**
     * constructor
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $this->setAttributes([
            'class'          => 'quiqqer-payments-control',
            'showInactive'   => true,
            'template'       => 'list',
            // Custom children template (path to html file); overwrites "template".
            'customTemplate' => false,
            // Custom children template css (path to css file); overwrites "template".
            'customCss'      => false,
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
        $Engine   = QUI::getTemplateManager()->getEngine();
        $Payments = \QUI\ERP\Accounting\Payments\Payments::getInstance();

        // load custom template (if set)
        if ($this->getAttribute('customTemplate')
            && \file_exists($this->getAttribute('customTemplate'))
        ) {
            if ($this->getAttribute('customCss')
                && \file_exists($this->getAttribute('customCss'))
            ) {
                $this->addCSSFile($this->getAttribute('customCss'));
            }

            return $Engine->fetch($this->getAttribute('customTemplate'));
        }

        // control template (if custom template not set)
        switch ($this->getAttribute('template')) {
            case 'list':
                $template = dirname(__FILE__) . '/Payments.List.html';
                $css      = dirname(__FILE__) . '/Payments.List.css';
                break;
            case 'grid':
            default:
                $template = dirname(__FILE__) . '/Payments.Grid.html';
                $css      = dirname(__FILE__) . '/Payments.Grid.css';
        }

        $Engine->assign([
            'payments'     => $Payments->getpayments(),
            'showInactive' => $this->getAttribute('showInactive')
        ]);

        $this->addCSSFile($css);

        return $Engine->fetch($template);
    }
}
