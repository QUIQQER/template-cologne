<?php

/**
 * This file contains QUI\TemplateCologne\Controls\Payments
 *
 * This control creates a list of payments that are configured in system.
 * You can show it a list or grid.
 *
 * @author Michael Danielczok (www.pcsg.de)
 */

namespace QUI\TemplateCologne\Controls;

use QUI;

use function class_exists;
use function file_exists;

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
    public function __construct(array $attributes = [])
    {
        $this->setAttributes([
            'class' => 'quiqqer-payments-control',
            'showInactive' => false,
            'template' => 'list',
            // Custom children template (path to html file); overwrites "template".
            'customTemplate' => false,
            // Custom children template css (path to css file); overwrites "template".
            'customCss' => false,
        ]);

        parent::__construct($attributes);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        if (!class_exists('\QUI\ERP\Accounting\Payments\Payments')) {
            return '';
        }

        $Engine = QUI::getTemplateManager()->getEngine();
        $Payments = QUI\ERP\Accounting\Payments\Payments::getInstance();
        $payments = $Payments->getpayments();

        if (count($payments) < 1) {
            QUI\System\Log::addWarning(
                "No payment methods were found in the system.\nControl: QUI\TemplateCologne\Controls\Payments"
            );
        }

        $Engine->assign([
            'payments' => $payments,
            'showInactive' => $this->getAttribute('showInactive')
        ]);

        // load custom template (if set)
        $customTemplate = $this->getAttribute('customTemplate');
        $customCss = $this->getAttribute('customCss');

        if ($customTemplate && file_exists($customTemplate)) {
            if ($customCss && file_exists($customCss)) {
                $this->addCSSFile($this->getAttribute('customCss'));
            }

            return $Engine->fetch($this->getAttribute('customTemplate'));
        }

        // control template (if custom template not set)
        switch ($this->getAttribute('template')) {
            case 'list':
                $template = dirname(__FILE__) . '/Payments.List.html';
                $css = dirname(__FILE__) . '/Payments.List.css';
                break;
            case 'grid':
            default:
                $template = dirname(__FILE__) . '/Payments.Grid.html';
                $css = dirname(__FILE__) . '/Payments.Grid.css';
        }

        $this->addCSSFile($css);

        return $Engine->fetch($template);
    }
}
