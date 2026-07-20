<?php

/**
 * This file contains package_quiqqer_template-cologne_ajax_getLangList
 */

/**
 * Return the lang list (html)
 *
 * @return string
 */
QUI::getAjax()->registerFunction(
    'package_quiqqer_template-cologne_ajax_getLangList',
    function (string $flagFolderPath, int $siteId): string {
        $Engine = QUI::getTemplateManager()->getEngine();
        $Project = QUI::getRewrite()->getProject();

        if (!$Project instanceof QUI\Projects\Project) {
            throw new QUI\Exception('No project available.');
        }

        $Site = $Project->get($siteId);
        $langs = $Project->getLanguages();

        if (count($langs) < 2) {
            return '';
        }

        $Engine->assign([
            'Site' => $Site,
            'projectLang' => $Project->getLang(),
            'DefaultCurrency' => QUI\ERP\Currency\Handler::getDefaultCurrency(),
            'langs' => $langs,
            'path' => $flagFolderPath
        ]);

        return QUI\Output::getInstance()->parse($Engine->fetch(dirname(__FILE__) . '/template/LangList.html'));
    },
    ['flagFolderPath', 'siteId']
);
