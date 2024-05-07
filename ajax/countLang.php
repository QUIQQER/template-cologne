<?php

/**
 * This file contains package_quiqqer_template-cologne_ajax_countLang
 */

/**
 * Return number of project languages
 *
 * @return number
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_template-cologne_ajax_countLang',
    function () {
        $Site = QUI::getRewrite()->getSite();
        $Project = $Site->getProject();

        return count($Project->getLanguages());
    }
);
