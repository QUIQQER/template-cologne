<?php

/**
 * This file contains package_quiqqer_template-cologne_ajax_countLang
 */

/**
 * Return number of project languages
 *
 * @return int
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_template-cologne_ajax_countLang',
    function (): int {
        $Site = QUI::getRewrite()->getSite();

        if (!$Site instanceof QUI\Interfaces\Projects\Site) {
            throw new QUI\Exception('No site available.');
        }

        $Project = $Site->getProject();

        return count($Project->getLanguages());
    }
);
