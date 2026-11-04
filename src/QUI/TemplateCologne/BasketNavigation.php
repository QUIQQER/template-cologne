<?php

namespace QUI\TemplateCologne;

use QUI;
use QUI\Projects\Project;
use Throwable;

/**
 * Cologne's basket click settings and frontend page destinations.
 */
class BasketNavigation
{
    private const CHECKOUT_SITE_TYPES = [
        'quiqqer/order-simple-checkout:types/simpleCheckout',
        'quiqqer/order:types/orderingProcess'
    ];

    /**
     * @return array{key: string, value: string, source: string}
     */
    public static function getAction(Project $Project, bool $mobile): array
    {
        $key = 'templateCologne.settings.' . ($mobile ? 'basketMobileAction' : 'basketAction');
        $value = $Project->getConfig($key);
        $allowed = $mobile
            ? ['openShoppingCart', 'openOrderProcessUrl']
            : ['openSmallBasket', 'openOrderProcessUrl', 'openOrderProcess', 'openShoppingCart'];
        $configured = in_array($value, $allowed, true);

        return [
            'key' => $key,
            'value' => $configured ? $value : ($mobile ? 'openShoppingCart' : 'openSmallBasket'),
            'source' => $configured ? 'configured' : 'default'
        ];
    }

    /**
     * Resolve page actions; log missing destinations only when clicked.
     */
    public static function getNavigationUrl(Project $Project, bool $mobile): ?string
    {
        $action = self::getAction($Project, $mobile);

        if (!in_array($action['value'], ['openShoppingCart', 'openOrderProcessUrl'], true)) {
            return null;
        }

        $siteTypes = self::CHECKOUT_SITE_TYPES;
        $actionLabel = 'Bestellseite öffnen';

        if ($action['value'] === 'openShoppingCart') {
            array_unshift($siteTypes, 'quiqqer/order:types/shoppingCart');
            $actionLabel = 'Warenkorbseite öffnen';
        }

        $failures = [];

        foreach ($siteTypes as $type) {
            try {
                $sites = $Project->getSitesIds([
                    'where' => [
                        'type' => $type,
                        'active' => 1,
                        'deleted' => 0
                    ]
                ]);

                if ($sites === []) {
                    $failures[$type][] = 'Keine aktive, nicht gelöschte Seite vorhanden.';
                }

                foreach ($sites as $site) {
                    $id = (int)$site['id'];

                    try {
                        $Site = $Project->get($id);

                        if (!$Site->hasPermission('quiqqer.projects.site.view')) {
                            $failures[$type][] = 'Seite ' . $id . ': kein Ansichtsrecht.';
                            continue;
                        }

                        $url = $Site->getUrlRewritten();

                        if ($url !== '') {
                            return $url;
                        }

                        $failures[$type][] = 'Seite ' . $id . ': keine URL verfügbar.';
                    } catch (Throwable $Exception) {
                        $failures[$type][] = 'Seite ' . $id . ': nicht verfügbar ('
                            . $Exception::class . ', Code ' . $Exception->getCode() . ').';
                    }
                }
            } catch (Throwable $Exception) {
                $failures[$type][] = 'Seitensuche fehlgeschlagen (' . $Exception::class
                    . ', Code ' . $Exception->getCode() . ').';
            }
        }

        QUI\System\Log::addError(
            'Template Cologne: Warenkorb-Navigation nicht möglich. Für die Klickaktion '
            . '„' . $actionLabel . '“ wurde keine verfügbare Zielseite gefunden. '
            . 'Bitte eine passende aktive Seite der aufgeführten Seitentypen anlegen '
            . 'oder deren Ansichtsrechte prüfen. Die Weiterleitung wurde abgebrochen.',
            [
                'project' => $Project->getName(),
                'language' => $Project->getLang(),
                'device' => $mobile ? 'mobile' : 'desktop',
                'setting' => $action['key'],
                'effectiveValue' => $action['value'],
                'valueSource' => $action['source'],
                'searchedSiteTypes' => $siteTypes,
                'failures' => $failures
            ]
        );

        return null;
    }
}
