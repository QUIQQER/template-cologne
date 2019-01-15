<?php

/**
 * This file contains QUI\TemplateCologne\Utils
 */

namespace QUI\TemplateCologne;

use QUI;

/**
 * Class Utils
 */
class Utils
{
    /**
     * Get user avatar. If no avatar available return false.
     *
     * @param $User QUI\Interfaces\Users\User
     * @throws QUI\Exception
     *
     * @return QUI\Projects\Media\Image|false
     */
    public static function getAvatar($User)
    {
        if (!$User instanceof QUI\Interfaces\Users\User) {
            throw new QUI\Exception([
                QUI::getLocale()->get(
                    'quiqqer/template-cologne',
                    'exception.user.required'
                )
            ]);
        }

        $result = QUI::getEvents()->fireEvent('userGetAvatar', [$User]);

        foreach ($result as $Entry) {
            if ($Entry instanceof QUI\Interfaces\Projects\Media\File) {
                return $Entry;
            }
        }

        $avatar = $User->getAttribute('avatar');

        if (!QUI\Projects\Media\Utils::isMediaUrl($avatar)) {
            return false;
        }

        try {
            return QUI\Projects\Media\Utils::getImageByUrl($avatar);
        } catch (QUI\Exception $Exception) {
        }

        return false;
    }

    /**
     * @param $params
     * @return array|bool|object|string
     * @throws QUI\Exception
     */
    public static function getConfig($params)
    {
        try {
            return QUI\Cache\Manager::get(
                'quiqqer/templateCologne/' . $params['Site']->getId()
            );
        } catch (QUI\Exception $Exception) {
        }

        $config = [];

        /* @var $Project QUI\Projects\Project */
        /* @var $Template QUI\Template() */
        $Project  = $params['Project'];
        $Template = $params['Template'];

        /**
         * no header?
         * no breadcrumb?
         * Body Class
         *
         * own site type
         */
        $header         = 'hide';
        $showBreadcrumb = false;
        $siteType       = 'no-sidebar';

        switch ($Template->getLayoutType()) {
            case 'layout/startPage':
                $header         = $Project->getConfig('templateCologne.settings.headerStartPage');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbStartPage');
                $siteType       = 'layout-start-page';
                break;

            case 'layout/noSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerNoSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebar');
                $siteType       = 'layout-no-sidebar';
                break;

            case 'layout/noSidebarThin':
                $header         = $Project->getConfig('templateCologne.settings.headerNoSidebarThin');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbNoSidebarThin');
                $siteType       = 'layout-no-sidebar';
                break;

            case 'layout/rightSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerRightSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbRightSidebar');
                $siteType       = 'layout-right-sidebar';
                break;

            case 'layout/leftSidebar':
                $header         = $Project->getConfig('templateCologne.settings.headerLeftSidebar');
                $showBreadcrumb = $Project->getConfig('templateCologne.settings.showBreadcrumbLeftSidebar');
                $siteType       = 'layout-left-sidebar';
                break;
        }

        $showPageTitle = $params['Site']->getAttribute('templateCologne.showTitle');
        $showPageShort = $params['Site']->getAttribute('templateCologne.showShort');

        /* site own show header */
        switch ($params['Site']->getAttribute('templateCologne.showEmotion')) {
            case 'show':
                $header = true;
                break;
            case 'hide':
                $header = false;
        }

        $settingsCSS = include 'settings.css.php';

        $config += [
            'header'         => $header,
            'showBreadcrumb' => $showBreadcrumb,
            'settingsCSS'    => '<style>' . $settingsCSS . '</style>',
            'typeClass'      => 'type-' . str_replace(['/', ':'], '-', $params['Site']->getAttribute('type')),
            'siteType'       => $siteType,
            'showPageTitle'  => $showPageTitle,
            'showPageShort'  => $showPageShort
        ];

        // set cache
        QUI\Cache\Manager::set(
            'quiqqer/templateCologne/' . $params['Site']->getId(),
            $config
        );

        return $config;
    }
}
