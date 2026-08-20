<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\TemplateCologne\Utils;

class UtilsConfigTest extends TestCase
{
    /** @var list<string> */
    private array $cacheNames = [];

    protected function tearDown(): void
    {
        foreach ($this->cacheNames as $cacheName) {
            QUI\Cache\Manager::clear($cacheName);
        }

        parent::tearDown();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function layoutProvider(): iterable
    {
        yield 'start page' => ['layout/startPage', 'layout-start-page'];
        yield 'wide content' => ['layout/noSidebar', 'layout-no-sidebar'];
        yield 'thin content' => ['layout/noSidebarThin', 'layout-no-sidebar'];
        yield 'right sidebar' => ['layout/rightSidebar', 'layout-right-sidebar'];
        yield 'left sidebar' => ['layout/leftSidebar', 'layout-left-sidebar'];
    }

    #[DataProvider('layoutProvider')]
    public function testConfigMapsEveryLayoutAndProducesSettingsCss(
        string $layout,
        string $expectedSiteType
    ): void {
        $config = $this->getConfig($layout);

        self::assertSame($expectedSiteType, $config['siteType']);
        self::assertSame('afterNav', $config['header']);
        self::assertSame('both', $config['pageTitle']);
        self::assertSame('enable', $config['settings.pageShortDesc']);
        self::assertSame('type-quiqqer-products-types-list', $config['typeClass']);
        self::assertSame('openSmallBasket', $config['basketAction']);
        self::assertStringContainsString('--qui-nav-height: 72px', $config['settingsCSS']);
        self::assertStringContainsString('max-width: 900px', $config['settingsCSS']);
    }

    /**
     * @return iterable<string, array{string, bool, bool, bool, bool}>
     */
    public static function checkoutAppearanceProvider(): iterable
    {
        yield 'compact' => ['compact', false, false, true, true];
        yield 'full' => ['full', true, false, true, true];
        yield 'minimal' => ['minimal', false, true, false, false];
        yield 'unknown falls back to minimal' => ['unknown', false, true, false, false];
    }

    #[DataProvider('checkoutAppearanceProvider')]
    public function testConfigAppliesCheckoutAppearance(
        string $appearance,
        bool $showBreadcrumb,
        bool $minimalDesign,
        bool $showNavigation,
        bool $showFooter
    ): void {
        $config = $this->getConfig(
            'layout/noSidebar',
            [
                'templateCologne.settings.checkoutAppearance' => $appearance
            ],
            [
                'type' => 'quiqqer/order:types/shoppingCart',
                'templateCologne.header' => false,
                'templateCologne.pageTitle' => false,
                'templateCologne.pageDesc' => false
            ]
        );

        self::assertSame($showBreadcrumb, $config['showBreadcrumb']);
        self::assertSame($minimalDesign, $config['minimalDesign']);
        self::assertSame($showNavigation, $config['showNav']);
        self::assertSame($showFooter, $config['showFooter']);
        self::assertSame(
            $minimalDesign ? 'type-minimal-design' : '',
            $config['minimalDesignClass']
        );
    }

    /**
     * @return iterable<string, array{string, bool, bool}>
     */
    public static function languageCurrencyProvider(): iterable
    {
        yield 'only currency' => ['onlyCurrency', false, true];
        yield 'only language' => ['onlyLang', true, false];
        yield 'disabled' => ['disabled', false, false];
        yield 'combined' => ['currencyAndLang', true, true];
        yield 'unknown defaults to combined' => ['unexpected', true, true];
    }

    #[DataProvider('languageCurrencyProvider')]
    public function testConfigMapsLanguageAndCurrencyVisibility(
        string $controlType,
        bool $showLanguage,
        bool $showCurrency
    ): void {
        $config = $this->getConfig('layout/noSidebar', [
            'templateCologne.settings.currencyLangSwitch.controlType' => $controlType
        ]);

        self::assertSame($showLanguage, $config['showTopbarLanguageSwitch']);
        self::assertSame($showCurrency, $config['showTopbarCurrencySwitch']);
    }

    public function testConfigAppliesNavigationAndApplicationOverrides(): void
    {
        $config = $this->getConfig(
            'layout/noSidebar',
            [
                'templateCologne.settings.basketStyle' => 'compact',
                'templateCologne.settings.basketAction' => 'openOrderProcessUrl',
                'templateCologne.settings.homeLink' => true,
                'templateCologne.settings.homeLinkText' => '{"en":"Store"}',
                'templateCologne.settings.showNavAfterScroll' => 120,
                'templateCologne.settings.setMenuPosBackOnInit' => true
            ],
            [
                'templateCologne.pageCustomClass' => 'seasonal',
                'templateCologne.largeSpacing' => true
            ],
            false,
            false
        );

        self::assertFalse($config['showTopBar']);
        self::assertFalse($config['showNav']);
        self::assertFalse($config['showFooter']);
        self::assertSame('compact', $config['basketStyle']);
        self::assertSame('openOrderProcessUrl', $config['basketAction']);
        self::assertTrue($config['homeLink']);
        self::assertSame('Store', $config['homeLinkText']);
        self::assertSame(120, $config['showNavAfterScroll']);
        self::assertTrue($config['showMenuSmooth']);
        self::assertTrue($config['setMenuPosBackOnInit']);
        self::assertSame('seasonal templateCologne__seasonal', $config['pageCustomClass']);
        self::assertStringContainsString('margin-bottom: 5em', $config['settingsCSS']);
    }

    public function testConfigBuildsLocalizedPredefinedFooterLinks(): void
    {
        $standardSites = [];
        $siteDefinitions = [
            'quiqqer/products:types/search' => ['Search', '/search'],
            'quiqqer/sitetypes:types/legalnotes' => ['Legal notes', '/legal'],
            'quiqqer/sitetypes:types/privacypolicy' => ['Privacy', '/privacy'],
            'quiqqer/sitetypes:types/generalTermsAndConditions' => ['Terms', '/terms']
        ];

        foreach ($siteDefinitions as $type => [$title, $url]) {
            $Site = $this->createMock(Site::class);
            $Site->method('getAttribute')->with('title')->willReturn($title);
            $Site->method('getUrlRewritten')->willReturn($url);
            $standardSites[$type] = [$Site];
        }

        $config = $this->getConfig(
            'layout/noSidebar',
            [
                'templateCologne.settings.predefinedFooter.shortText' => true,
                'templateCologne.settings.predefinedFooter.shortText.title' => '{"en":"About us"}',
                'templateCologne.settings.predefinedFooter.urlList' => true,
                'templateCologne.settings.predefinedFooter.urlList.title' => '{"en":"Useful links"}',
                'templateCologne.settings.predefinedFooter.urlList.showStandardSites' => true
            ],
            [],
            true,
            true,
            $standardSites
        );

        self::assertSame(['title' => 'About us'], $config['shortText']);
        self::assertSame('Useful links', $config['urlList']['title']);
        self::assertSame([], $config['urlList']['sites']);
        self::assertSame(['title' => 'Search', 'url' => '/search'], $config['urlList']['productSearch']);
        self::assertSame(['title' => 'Legal notes', 'url' => '/legal'], $config['urlList']['legalNotes']);
        self::assertSame(['title' => 'Privacy', 'url' => '/privacy'], $config['urlList']['privacyPolicy']);
        self::assertSame(['title' => 'Terms', 'url' => '/terms'], $config['urlList']['generalTermsAndConditions']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getConfig(
        string $layout,
        array $settingsOverrides = [],
        array $attributeOverrides = [],
        bool $templateHeader = true,
        bool $templateFooter = true,
        array $standardSites = []
    ): array {
        $settings = [
            'templateCologne.settings.logoHeight' => '72',
            'templateCologne.settings.headerStartPage' => 'beforeContent',
            'templateCologne.settings.pageTitleStartPage' => 'header',
            'templateCologne.settings.shortDescStartPage' => true,
            'templateCologne.settings.showBreadcrumbStartPage' => true,
            'templateCologne.settings.headerNoSidebar' => 'beforeContent',
            'templateCologne.settings.pageTitleNoSidebar' => 'header',
            'templateCologne.settings.shortDescNoSidebar' => true,
            'templateCologne.settings.showBreadcrumbNoSidebar' => true,
            'templateCologne.settings.headerNoSidebarThin' => 'beforeContent',
            'templateCologne.settings.pageTitleNoSidebarThin' => 'header',
            'templateCologne.settings.shortDescNoSidebarThin' => true,
            'templateCologne.settings.showBreadcrumbNoSidebarThin' => true,
            'templateCologne.settings.headerRightSidebar' => 'beforeContent',
            'templateCologne.settings.pageTitleRightSidebar' => 'header',
            'templateCologne.settings.shortDescLeftSidebar' => true,
            'templateCologne.settings.showBreadcrumbRightSidebar' => true,
            'templateCologne.settings.headerLeftSidebar' => 'beforeContent',
            'templateCologne.settings.pageTitleLeftSidebar' => 'header',
            'templateCologne.settings.shortDescRightSidebar' => true,
            'templateCologne.settings.showBreadcrumbLeftSidebar' => true,
            'templateCologne.settings.basketAction' => 'invalid-action',
            'mobileMenu.settings.breakPoint' => 900,
            'templateCologne.settings.headerImagePosition' => 'center center',
            'templateCologne.settings.headerHeight' => 320,
            'templateCologne.settings.currencyLangSwitch.controlType' => 'currencyAndLang'
        ];

        $settings = array_replace($settings, $settingsOverrides);
        $projectName = 'phpunit-template-cologne-' . md5(serialize([
            $layout,
            $settingsOverrides,
            $attributeOverrides,
            $templateHeader,
            $templateFooter
        ]));
        $Project = $this->createMock(Project::class);
        $Project->method('getName')->willReturn($projectName);
        $Project->method('getLang')->willReturn('en');
        $Project->method('getConfig')->willReturnCallback(
            static fn(string $name): mixed => $settings[$name] ?? false
        );
        $Project->method('getSites')->willReturnCallback(
            static function (array $params) use ($standardSites): array {
                $type = $params['where']['type']['value'] ?? '';

                return $standardSites[$type] ?? [];
            }
        );

        $attributes = [
            'layout' => $layout,
            'type' => 'quiqqer/products:types/list',
            'templateCologne.header' => 'afterNav',
            'templateCologne.pageTitle' => 'both',
            'templateCologne.pageDesc' => 'enable',
            'templateCologne.pageCustomClass' => false,
            'templateCologne.largeSpacing' => false
        ];
        $attributes = array_replace($attributes, $attributeOverrides);
        $Site = $this->createMock(Site::class);
        $Site->method('getId')->willReturn(crc32($layout));
        $Site->method('getAttribute')->willReturnCallback(
            static fn(string $name): mixed => $attributes[$name] ?? false
        );

        $Template = $this->createMock(QUI\Template::class);
        $Template->method('getAttribute')->willReturnCallback(
            static fn(string $name): bool => match ($name) {
                'template-header' => $templateHeader,
                'template-footer' => $templateFooter,
                default => false
            }
        );

        $cacheName = 'quiqqer/templateCologne/' . md5(
            $Site->getId() . $Project->getName() . $Project->getLang()
        );
        $this->cacheNames[] = $cacheName;
        QUI\Cache\Manager::clear($cacheName);

        return Utils::getConfig([
            'Site' => $Site,
            'Project' => $Project,
            'Template' => $Template
        ]);
    }
}
