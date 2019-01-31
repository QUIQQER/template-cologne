<?php

/**
 * This file contains package_quiqqer_template-cologne_ajax_getLangList
 */

/**
 * Return the lang list (html)
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_template-cologne_ajax_getLangList',
    function ($flagFolderPath) {

        $Engine = QUI::getTemplateManager()->getEngine();
        $Site   = QUI::getRewrite()->getSite();;
        $Project = $Site->getProject();
        $Project->getLanguages();
        $langs = $Project->getLanguages();

        if (count($langs) < 2) {
            return '';
        }

        $Engine->assign([
            'Site'            => $Site,
            'projectLang'     => $Project->getLang(),
            'DefaultCurrency' => QUI\ERP\Currency\Handler::getDefaultCurrency(),
            'langs'           => $langs,
            'path'            => $flagFolderPath
        ]);

        return $Engine->fetch(dirname(__FILE__) . '/template/LangList.html');
    },
    ['flagFolderPath']
);
