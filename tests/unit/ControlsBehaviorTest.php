<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Currency\Currency;
use QUI\ERP\Currency\Handler as CurrencyHandler;
use QUI\Interfaces\Template\EngineInterface;
use QUI\Package\Manager as PackageManager;
use QUI\Package\Package;
use QUI\Projects\Media;
use QUI\Projects\Media\Image;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\Rewrite;
use QUI\TemplateCologne\Controls\CurrencySwitch;
use QUI\TemplateCologne\Controls\LangCurrencySwitch;
use QUI\TemplateCologne\Controls\LoginAndRegister;
use QUI\TemplateCologne\Controls\Menu\Categories;
use QUI\TemplateCologne\Controls\Payments;
use QUI\TemplateCologne\Controls\ProductGallery;
use QUI\TemplateCologne\Controls\SimpleUserInfo;
use QUI\Utils\Singleton;
use ReflectionProperty;

class ControlsBehaviorTest extends TestCase
{
    private ?QUI\Template $originalTemplate;
    private ?PackageManager $originalPackageManager;
    private ?Rewrite $originalRewrite;
    private ?QUI\Events\Manager $originalEvents;

    /** @var array<string, mixed> */
    private array $originalCurrencyState;

    /** @var array<array-key, mixed> */
    private array $originalSingletons;

    /** @var list<string> */
    private array $cacheNames = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalTemplate = QUI::$Template;
        $this->originalPackageManager = QUI::$PackageManager;
        $this->originalRewrite = QUI::$Rewrite;
        $this->originalEvents = QUI::$Events;
        $this->originalCurrencyState = $this->currencyState();
        $this->originalSingletons = $this->singletonProperty()->getValue();
    }

    protected function tearDown(): void
    {
        foreach ($this->cacheNames as $cacheName) {
            QUI\Cache\Manager::clear($cacheName);
        }

        QUI::$Template = $this->originalTemplate;
        QUI::$PackageManager = $this->originalPackageManager;
        QUI::$Rewrite = $this->originalRewrite;
        QUI::$Events = $this->originalEvents;
        $this->setCurrencyState($this->originalCurrencyState);
        $this->singletonProperty()->setValue(null, $this->originalSingletons);

        parent::tearDown();
    }

    public function testCurrencySwitchRendersRuntimeCurrencyAndControlOptions(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'currency-switch');
        $Currency = $this->configureCurrencies(['EUR'], false);
        $Control = new CurrencySwitch(['userRelatedCurrency' => 0]);

        self::assertSame('currency-switch', $Control->getBody());
        self::assertSame($Currency, $assigned['DefaultCurrency']);
        self::assertSame($Control, $assigned['this']);
        self::assertSame(1, $Control->getAttribute('data-qui-options-buttonshowsign'));
        self::assertSame(1, $Control->getAttribute('data-qui-options-dropdownshowsign'));
        self::assertSame(0, $Control->getAttribute('data-qui-options-showarrow'));
        self::assertSame('right', $Control->getAttribute('data-qui-options-dropdownposition'));
        self::assertFalse($Control->getAttribute('qui-class'));
    }

    public function testCurrencySwitchEnablesInteractiveControlForMultipleCurrencies(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'currency-switch-enabled');
        $this->configureCurrencies(['EUR', 'USD'], true);
        $Control = new CurrencySwitch();

        self::assertSame('currency-switch-enabled', $Control->getBody());
        self::assertSame(
            'package/quiqqer/currency/bin/controls/Switch',
            $Control->getAttribute('qui-class')
        );
    }

    public function testLanguageCurrencySwitchCombinesBothAvailableChoices(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'language-currency-switch');
        $Currency = $this->configureCurrencies(['EUR', 'USD'], true);
        $Project = $this->createMock(Project::class);
        $Project->method('getLanguages')->willReturn(['en', 'de']);
        $Project->method('getLang')->willReturn('en');
        $Site = $this->createMock(Site::class);
        $Site->method('getProject')->willReturn($Project);
        $Control = new LangCurrencySwitch([
            'Site' => $Site,
            'flagFolder' => '/flags/'
        ]);

        self::assertSame('language-currency-switch', $Control->getBody());
        self::assertTrue($assigned['currencySwitch']);
        self::assertTrue($assigned['enableChange']);
        self::assertSame('en', $assigned['projectLang']);
        self::assertSame('/flags/', $assigned['flagFolderPath']);
        self::assertSame($Currency, $assigned['DefaultCurrency']);
        self::assertSame('1', $Control->getAttribute('data-qui-options-userrelatedcurrency'));
        self::assertSame('/flags/', $Control->getAttribute('data-qui-options-flag-folder'));
        self::assertNotSame('', $assigned['imgAltText']);
    }

    public function testLanguageCurrencySwitchStaysStaticWithOneLanguageAndCurrency(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'language-currency-static');
        $this->configureCurrencies(['EUR'], false);
        $Project = $this->createMock(Project::class);
        $Project->method('getLanguages')->willReturn(['en']);
        $Project->method('getLang')->willReturn('missing-phpunit-language');
        $Site = $this->createMock(Site::class);
        $Site->method('getProject')->willReturn($Project);
        $Control = new LangCurrencySwitch([
            'Site' => $Site,
            'userRelatedCurrency' => 0
        ]);

        self::assertSame('language-currency-static', $Control->getBody());
        self::assertFalse($assigned['currencySwitch']);
        self::assertFalse($assigned['enableChange']);
        self::assertSame('0', $Control->getAttribute('data-qui-options-userrelatedcurrency'));
        self::assertFalse($Control->getAttribute('data-qui-options-flag-folder'));
    }

    public function testLanguageCurrencySwitchRequiresSite(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getSite')->willReturn(null);
        QUI::$Rewrite = $Rewrite;
        $Control = new LangCurrencySwitch();

        $this->expectException(QUI\Exception::class);
        $this->expectExceptionMessage('No site available.');

        $Control->getBody();
    }

    public function testLoginAndRegistrationControlAssignsBothForms(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'login-and-register');
        $Control = new LoginAndRegister();

        self::assertSame('login-and-register', $Control->getBody());
        self::assertInstanceOf(QUI\FrontendUsers\Controls\Login::class, $assigned['Login']);
        self::assertTrue($assigned['Login']->getAttribute('header'));
        self::assertTrue($assigned['Login']->getAttribute('passwordReset'));
        self::assertInstanceOf(
            QUI\FrontendUsers\Controls\RegistrationSignUp::class,
            $assigned['Registration']
        );
        self::assertFalse($assigned['Registration']->getAttribute('content'));
    }

    public function testPaymentsControlRendersListGridAndCustomTemplates(): void
    {
        $Payment = $this->createMock(QUI\ERP\Accounting\Payments\Types\Payment::class);
        $PaymentsManager = $this->createMock(QUI\ERP\Accounting\Payments\Payments::class);
        $PaymentsManager->method('getPayments')->willReturn([$Payment]);
        $this->useSingleton(QUI\ERP\Accounting\Payments\Payments::class, $PaymentsManager);

        $assigned = [];
        $this->useEngine(
            $assigned,
            'payments',
            static fn(string $file): string => basename($file)
        );

        $List = new Payments(['showInactive' => true]);
        self::assertSame('Payments.List.html', $List->getBody());
        self::assertSame([$Payment], $assigned['payments']);
        self::assertTrue($assigned['showInactive']);
        self::assertStringEndsWith('Payments.List.css', $List->getCSSFiles()[0]);

        $Grid = new Payments(['template' => 'grid']);
        self::assertSame('Payments.Grid.html', $Grid->getBody());
        self::assertStringEndsWith('Payments.Grid.css', $Grid->getCSSFiles()[0]);

        $Custom = new Payments([
            'customTemplate' => dirname(__DIR__, 2) . '/src/QUI/TemplateCologne/Controls/Payments.List.html',
            'customCss' => dirname(__DIR__, 2) . '/src/QUI/TemplateCologne/Controls/Payments.List.css'
        ]);
        self::assertSame('Payments.List.html', $Custom->getBody());
        self::assertStringEndsWith('Payments.List.css', $Custom->getCSSFiles()[0]);
    }

    public function testProductGalleryRejectsMissingProduct(): void
    {
        self::assertSame('', (new ProductGallery())->getBody());
    }

    public function testProductGalleryAssignsConfiguredSlider(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'product-gallery');
        $Placeholder = $this->createMock(Image::class);
        $Placeholder->method('getSizeCacheUrl')->willReturn('/placeholder.webp');
        $Media = $this->createMock(Media::class);
        $Media->method('getPlaceholderImage')->willReturn($Placeholder);
        $Project = $this->createMock(Project::class);
        $Project->method('getMedia')->willReturn($Media);
        $Product = $this->createMock(QUI\ERP\Products\Product\Product::class);
        $Product->method('getType')->willReturn(QUI\ERP\Products\Product\Types\Product::class);
        $Product->method('getFieldValue')->willReturn(51);
        $Control = new ProductGallery([
            'Product' => $Product,
            'Project' => $Project,
            'height' => '520px'
        ]);

        self::assertSame('product-gallery', $Control->getBody());
        self::assertInstanceOf(QUI\Gallery\Controls\Slider::class, $assigned['Gallery']);
        self::assertSame('/placeholder.webp', $assigned['Gallery']->getAttribute('placeholderimage'));
        self::assertSame('#fff', $assigned['Gallery']->getAttribute('placeholdercolor'));
        self::assertSame(51, $assigned['Gallery']->getAttribute('folderId'));
        self::assertSame('520px', $assigned['Gallery']->getAttribute('height'));
        self::assertSame(1, $assigned['Gallery']->getAttribute('data-qui-options-preview'));
        self::assertSame(0, $assigned['Gallery']->getAttribute('data-qui-options-show-title'));
    }

    public function testSimpleUserInfoValidatesExplicitUserAndSite(): void
    {
        $Control = new SimpleUserInfo();
        $User = $this->createMock(QUI\Interfaces\Users\User::class);
        $Site = $this->createMock(QUI\Interfaces\Projects\Site::class);
        $Control->setAttribute('User', $User);
        $Control->setAttribute('Site', $Site);

        self::assertSame($User, $Control->getUser());
        self::assertSame($Site, $Control->getSite());

        $Control = new SimpleUserInfo(['User' => 'invalid-user-value']);
        $this->expectException(QUI\FrontendUsers\Exception::class);
        $Control->getUser();
    }

    public function testSimpleUserInfoRequiresResolvableSite(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getSite')->willReturn(null);
        QUI::$Rewrite = $Rewrite;

        $this->expectException(QUI\Exception::class);
        $this->expectExceptionMessage('No site available.');

        (new SimpleUserInfo())->getSite();
    }

    public function testSimpleUserInfoRendersUserSummary(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'simple-user-info');
        $Events = $this->createMock(QUI\Events\Manager::class);
        $Events->method('fireEvent')->willReturn([]);
        QUI::$Events = $Events;
        $User = $this->createMock(QUI\Interfaces\Users\User::class);
        $User->method('getName')->willReturn('Ada');
        $User->method('getAttribute')->willReturnCallback(
            static fn(string $name): mixed => $name === 'regdate' ? '2024-01-02' : false
        );
        $OrderHandler = $this->createMock(QUI\ERP\Order\Handler::class);
        $OrderHandler->expects($this->once())
            ->method('countOrdersByUser')
            ->with($User)
            ->willReturn(4);
        $this->useSingleton(QUI\ERP\Order\Handler::class, $OrderHandler);
        $Control = new SimpleUserInfo(['User' => $User]);

        self::assertSame('simple-user-info', $Control->getBody());
        self::assertSame('Ada', $assigned['name']);
        self::assertSame('2024-01-02', $assigned['registrationDay']);
        self::assertSame(4, $assigned['ordersNumber']);
        self::assertStringEndsWith('avatar-placeholder.svg', $assigned['avatarUrl']);
    }

    public function testCategoriesReturnsEmptyWhenStartSiteCannotBeResolved(): void
    {
        $assigned = [];
        $this->useEngine($assigned, 'unused');
        $Project = $this->createMock(Project::class);
        $Project->method('get')->willThrowException(new QUI\Exception('missing start site'));
        $Control = new Categories([
            'Project' => $Project,
            'startId' => 9999
        ]);

        self::assertSame('', $Control->getBody());
        self::assertSame([], $assigned);
    }

    public function testCategoriesRendersAndCachesMenu(): void
    {
        $assigned = [];
        $Engine = $this->useEngine($assigned, 'rendered-menu');
        $Engine->expects($this->once())->method('fetch');
        $Start = $this->createMock(Site::class);
        $Project = $this->createMock(Project::class);
        $Project->expects($this->once())->method('get')->with(5)->willReturn($Start);
        $Current = $this->createMock(Site::class);
        $Current->method('getCachePath')->willReturn('/phpunit/current-site');
        $Packages = $this->createMock(PackageManager::class);
        $Packages->method('isInstalled')->with('quiqqer/order')->willReturn(false);
        QUI::$PackageManager = $Packages;
        $Control = new Categories([
            'Project' => $Project,
            'Site' => $Current,
            'startId' => 5,
            'showBasketButton' => true
        ]);
        $cacheName = QUI\Menu\EventHandler::menuCacheName() . '/megaMenu/' . md5(
            '/phpunit/current-site' . serialize($Control->getAttributes())
        );
        $this->cacheNames[] = $cacheName;
        QUI\Cache\Manager::clear($cacheName);

        self::assertSame('rendered-menu', $Control->getBody());
        self::assertSame($Start, $assigned['Site']);
        self::assertSame($Project, $assigned['Project']);
        self::assertFalse($assigned['showBasketButton']);
    }

    /**
     * @param array<string, mixed> $assigned
     */
    private function useEngine(
        array &$assigned,
        string $result,
        ?callable $fetch = null
    ): EngineInterface {
        $Engine = $this->createMock(EngineInterface::class);
        $Engine->method('assign')->willReturnCallback(
            static function (array|string $values, mixed $value = false) use (&$assigned): void {
                if (is_array($values)) {
                    $assigned = array_replace($assigned, $values);
                    return;
                }

                $assigned[$values] = $value;
            }
        );
        if ($fetch === null) {
            $Engine->method('fetch')->willReturn($result);
        } else {
            $Engine->method('fetch')->willReturnCallback($fetch);
        }
        $Template = $this->createMock(QUI\Template::class);
        $Template->method('getEngine')->willReturn($Engine);
        QUI::$Template = $Template;

        return $Engine;
    }

    /**
     * @param list<string> $codes
     */
    private function configureCurrencies(array $codes, bool $userRelatedCurrency): Currency
    {
        $data = [];

        foreach ($codes as $code) {
            $data[$code] = [
                'currency' => $code,
                'rate' => 1,
                'autoupdate' => 0,
                'precision' => 2,
                'type' => CurrencyHandler::CURRENCY_TYPE_DEFAULT,
                'customData' => null
            ];
        }

        $Currency = new Currency($data[$codes[0]], QUI::getLocale());
        $Config = $this->createMock(QUI\Config::class);
        $Config->method('getValue')->willReturnCallback(
            static function (string $section, string $key) use ($codes, $userRelatedCurrency): mixed {
                if ($section === 'currency' && $key === 'allowedCurrencies') {
                    return implode(',', $codes);
                }

                if ($section === 'general' && $key === 'userRelatedCurrency') {
                    return $userRelatedCurrency;
                }

                return false;
            }
        );
        $Package = $this->createMock(Package::class);
        $Package->method('getConfig')->willReturn($Config);
        $Packages = $this->createMock(PackageManager::class);
        $Packages->method('getInstalledPackage')->willReturn($Package);
        QUI::$PackageManager = $Packages;
        $this->setCurrencyState([
            'currencies' => $data,
            'Default' => $Currency,
            'RuntimeCurrency' => $Currency
        ]);

        return $Currency;
    }

    private function useSingleton(string $className, object $Instance): void
    {
        $instances = $this->singletonProperty()->getValue();
        $instances[$className] = $Instance;
        $this->singletonProperty()->setValue(null, $instances);
    }

    /** @return array<string, mixed> */
    private function currencyState(): array
    {
        $Reflection = new \ReflectionClass(CurrencyHandler::class);

        return [
            'currencies' => $Reflection->getProperty('currencies')->getValue(),
            'Default' => $Reflection->getProperty('Default')->getValue(),
            'RuntimeCurrency' => $Reflection->getProperty('RuntimeCurrency')->getValue()
        ];
    }

    /** @param array<string, mixed> $state */
    private function setCurrencyState(array $state): void
    {
        $Reflection = new \ReflectionClass(CurrencyHandler::class);

        foreach ($state as $property => $value) {
            $Reflection->getProperty($property)->setValue(null, $value);
        }
    }

    private function singletonProperty(): ReflectionProperty
    {
        return new ReflectionProperty(Singleton::class, 'instances');
    }
}
