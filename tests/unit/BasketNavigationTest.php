<?php

namespace QUI\TemplateCologne\Tests\Unit;

use Monolog\Handler\TestHandler;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\TemplateCologne\BasketNavigation;

class BasketNavigationTest extends TestCase
{
    private TestHandler $Log;
    private mixed $originalWarningLevel;
    private mixed $originalErrorLevel;

    protected function setUp(): void
    {
        $Config = QUI::getConfig('etc/plugins/quiqqer/log.ini.php');
        $this->originalWarningLevel = $Config->get('log_levels', 'warning');
        $this->originalErrorLevel = $Config->get('log_levels', 'error');
        $Config->set('log_levels', 'warning', 0);
        $Config->set('log_levels', 'error', 1);
        $this->Log = new TestHandler(bubble: false);
        QUI\Log\Logger::getLogger()->pushHandler($this->Log);
    }

    protected function tearDown(): void
    {
        QUI\Log\Logger::getLogger()->popHandler();
        QUI::getConfig('etc/plugins/quiqqer/log.ini.php')->set(
            'log_levels',
            'warning',
            $this->originalWarningLevel
        );
        QUI::getConfig('etc/plugins/quiqqer/log.ini.php')->set(
            'log_levels',
            'error',
            $this->originalErrorLevel
        );
    }

    public static function actionProvider(): iterable
    {
        yield 'existing desktop default' => [false, false, 'openSmallBasket', 'default'];
        yield 'existing shop mobile default' => [true, false, 'openShoppingCart', 'default'];
        yield 'invalid mobile value' => [true, 'openSmallBasket', 'openShoppingCart', 'default'];
        yield 'explicit mobile checkout' => [true, 'openOrderProcessUrl', 'openOrderProcessUrl', 'configured'];
        yield 'explicit desktop cart' => [false, 'openShoppingCart', 'openShoppingCart', 'configured'];
    }

    #[DataProvider('actionProvider')]
    public function testSettingsPreserveExplicitChoicesAndUseDeviceDefaults(
        bool $mobile,
        mixed $stored,
        string $effective,
        string $source
    ): void {
        $Project = $this->project($stored);

        self::assertSame([
            'key' => 'templateCologne.settings.' . ($mobile ? 'basketMobileAction' : 'basketAction'),
            'value' => $effective,
            'source' => $source
        ], BasketNavigation::getAction($Project, $mobile));
    }

    public static function destinationProvider(): iterable
    {
        yield 'cart before both checkouts' => [[1, 2, 3], '/cart'];
        yield 'simple checkout before order' => [[2, 3], '/simple'];
        yield 'simple checkout alone' => [[3], '/simple'];
        yield 'order alone' => [[2], '/order'];
    }

    #[DataProvider('destinationProvider')]
    public function testCartActionUsesAvailablePagesInOrder(array $available, string $expected): void
    {
        $Project = $this->project('openShoppingCart', $available);

        self::assertSame($expected, BasketNavigation::getNavigationUrl($Project, false));
        self::assertSame([], $this->Log->getRecords());
    }

    public static function checkoutDestinationProvider(): iterable
    {
        yield 'simple checkout before order and cart' => [[1, 2, 3], '/simple'];
        yield 'order when simple checkout is missing' => [[1, 2], '/order'];
        yield 'cart is not a checkout fallback' => [[1], null];
    }

    #[DataProvider('checkoutDestinationProvider')]
    public function testCheckoutActionSkipsCartAndPrefersSimpleCheckout(array $available, ?string $expected): void
    {
        foreach ([false, true] as $mobile) {
            self::assertSame(
                $expected,
                BasketNavigation::getNavigationUrl($this->project('openOrderProcessUrl', $available), $mobile)
            );
        }
    }

    public function testPopupActionsDoNotResolvePageDestinations(): void
    {
        $Project = $this->project('openSmallBasket');
        $Project->expects(self::never())->method('getSitesIds');

        self::assertNull(BasketNavigation::getNavigationUrl($Project, false));
        self::assertSame([], $this->Log->getRecords());
    }

    public function testInaccessibleSimpleCheckoutFallsBackToOrder(): void
    {
        self::assertSame(
            '/order',
            BasketNavigation::getNavigationUrl($this->project('openOrderProcessUrl', [2, 3], [3]), true)
        );
        self::assertSame([], $this->Log->getRecords());
    }

    public function testMissingCheckoutLogsOnlyCheckoutTypesAndCorrectAction(): void
    {
        self::assertNull(BasketNavigation::getNavigationUrl($this->project('openOrderProcessUrl', [1]), true));
        $records = $this->Log->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Error, $records[0]->level);
        self::assertSame('openOrderProcessUrl', $records[0]->context['effectiveValue']);
        self::assertSame([
            'quiqqer/order-simple-checkout:types/simpleCheckout',
            'quiqqer/order:types/orderingProcess'
        ], $records[0]->context['searchedSiteTypes']);
        self::assertCount(2, $records[0]->context['failures']);
        self::assertStringContainsString('Bestellseite öffnen', $records[0]->message);
    }

    public function testUnavailableCartIsSkippedForAccessibleOrder(): void
    {
        $Project = $this->project('openShoppingCart', [1, 2], [1]);

        self::assertSame('/order', BasketNavigation::getNavigationUrl($Project, true));
        self::assertSame([], $this->Log->getRecords());
    }

    public function testMissingDestinationsLogSettingAndDefaultSourceWithoutThrowing(): void
    {
        self::assertNull(BasketNavigation::getNavigationUrl($this->project(false), true));
        $records = $this->Log->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Error, $records[0]->level);
        $context = $records[0]->context;
        self::assertSame('basket-test', $context['project']);
        self::assertSame('de', $context['language']);
        self::assertSame('mobile', $context['device']);
        self::assertSame('templateCologne.settings.basketMobileAction', $context['setting']);
        self::assertSame('openShoppingCart', $context['effectiveValue']);
        self::assertSame('default', $context['valueSource']);
        self::assertCount(3, $context['searchedSiteTypes']);
        self::assertCount(3, $context['failures']);
        self::assertStringContainsString('Weiterleitung wurde abgebrochen', $records[0]->message);
    }

    public function testLookupFailureIsCaughtAndRecordedWithoutExceptionDetails(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')->willReturn('openShoppingCart');
        $Project->method('getName')->willReturn('basket-test');
        $Project->method('getLang')->willReturn('de');
        $Project->method('getSitesIds')->willThrowException(new \RuntimeException('private database details'));

        self::assertNull(BasketNavigation::getNavigationUrl($Project, false));
        $records = $this->Log->getRecords();
        self::assertCount(1, $records);
        self::assertSame(Level::Error, $records[0]->level);
        self::assertSame('configured', $records[0]->context['valueSource']);
        self::assertStringNotContainsString('private database details', json_encode($records[0]->context));
    }

    private function project(mixed $stored, array $available = [], array $denied = []): Project
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')->willReturn($stored);
        $Project->method('getName')->willReturn('basket-test');
        $Project->method('getLang')->willReturn('de');
        $types = [
            'quiqqer/order:types/shoppingCart' => 1,
            'quiqqer/order:types/orderingProcess' => 2,
            'quiqqer/order-simple-checkout:types/simpleCheckout' => 3
        ];
        $Project->method('getSitesIds')->willReturnCallback(
            static function (array $params) use ($available, $types): array {
                self::assertSame(1, $params['where']['active']);
                self::assertSame(0, $params['where']['deleted']);
                $id = $types[$params['where']['type']];
                return in_array($id, $available, true) ? [['id' => $id]] : [];
            }
        );
        $Project->method('get')->willReturnCallback(function (int $id) use ($denied): Site {
            $Site = $this->createMock(Site::class);
            $Site->method('hasPermission')->willReturn(!in_array($id, $denied, true));
            $Site->method('getUrlRewritten')->willReturn([1 => '/cart', 2 => '/order', 3 => '/simple'][$id]);
            return $Site;
        });

        return $Project;
    }
}
