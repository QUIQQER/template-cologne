<?php

QUI::getAjax()->registerFunction(
    'package_quiqqer_template-cologne_ajax_getNavigationUrl',
    function ($project, $mobile): ?string {
        $Project = QUI::getProjectManager()->decode($project);

        return QUI\TemplateCologne\BasketNavigation::getNavigationUrl($Project, $mobile === '1');
    },
    ['project', 'mobile']
);
