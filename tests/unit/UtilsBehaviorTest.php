<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\Events\Manager as EventsManager;
use QUI\Package\Manager as PackageManager;
use QUI\Projects\Media\Image;
use QUI\Projects\Project;
use QUI\Rewrite;
use QUI\TemplateCologne\Utils;
use ReflectionProperty;

class UtilsBehaviorTest extends TestCase
{
    private ?EventsManager $originalEvents;
    private ?PackageManager $originalPackageManager;
    private ?Rewrite $originalRewrite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalEvents = QUI::$Events;
        $this->originalPackageManager = QUI::$PackageManager;
        $this->originalRewrite = QUI::$Rewrite;
    }

    protected function tearDown(): void
    {
        QUI::$Events = $this->originalEvents;
        QUI::$PackageManager = $this->originalPackageManager;
        QUI::$Rewrite = $this->originalRewrite;

        parent::tearDown();
    }

    public function testAvatarRejectsValuesThatAreNotUsers(): void
    {
        $this->expectException(QUI\Exception::class);

        Utils::getAvatar('not-a-user');
    }

    public function testAvatarUsesFirstImageReturnedByEventListeners(): void
    {
        $User = $this->createMock(QUI\Interfaces\Users\User::class);
        $Image = $this->createMock(Image::class);
        $Events = $this->createMock(EventsManager::class);
        $Events->expects($this->once())
            ->method('fireEvent')
            ->with('userGetAvatar', [$User])
            ->willReturn(['ignored', $Image]);
        QUI::$Events = $Events;

        self::assertSame($Image, Utils::getAvatar($User));
    }

    public function testAvatarReturnsFalseWhenUserHasNoMediaUrl(): void
    {
        $User = $this->createMock(QUI\Interfaces\Users\User::class);
        $User->expects($this->once())
            ->method('getAttribute')
            ->with('avatar')
            ->willReturn('plain-avatar-value');
        $Events = $this->createMock(EventsManager::class);
        $Events->method('fireEvent')->willReturn([]);
        QUI::$Events = $Events;

        self::assertFalse(Utils::getAvatar($User));
    }

    public function testSettingRejectsEmptyName(): void
    {
        self::assertFalse(Utils::getSetting(''));
    }

    public function testSettingPrefixesShortNameAndUsesCurrentProject(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->expects($this->once())
            ->method('getConfig')
            ->with('templateCologne.settings.homeLink')
            ->willReturn('configured');
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;

        self::assertSame('configured', Utils::getSetting('homeLink'));
    }

    public function testSettingKeepsFullyQualifiedName(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->expects($this->once())
            ->method('getConfig')
            ->with('templateCologne.settings.logoHeight')
            ->willReturn(88);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;

        self::assertSame(88, Utils::getSetting('templateCologne.settings.logoHeight'));
    }

    public function testSettingReturnsEmptyStringWithoutProject(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn(null);
        QUI::$Rewrite = $Rewrite;

        self::assertSame('', Utils::getSetting('homeLink'));
    }

    public function testSettingReturnsEmptyStringWhenRewriteFails(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willThrowException(new \RuntimeException('rewrite failed'));
        QUI::$Rewrite = $Rewrite;

        self::assertSame('', Utils::getSetting('homeLink'));
    }

    public function testStockViewRequiresProjectAndEnabledSetting(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn(null);
        QUI::$Rewrite = $Rewrite;

        self::assertFalse(Utils::getStockFrontendView(1001));

        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')->with('templateCologne.settings.showStock')->willReturn(false);
        $Rewrite->method('getProject')->willReturn($Project);

        self::assertFalse(Utils::getStockFrontendView(1001));
    }

    public function testStockViewRequiresInstalledStockPackage(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')->with('templateCologne.settings.showStock')->willReturn(true);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        $Packages = $this->createMock(PackageManager::class);
        $Packages->expects($this->once())
            ->method('isInstalled')
            ->with('quiqqer/stock-management')
            ->willReturn(false);
        QUI::$Rewrite = $Rewrite;
        QUI::$PackageManager = $Packages;

        self::assertFalse(Utils::getStockFrontendView(1002));
    }

    public function testShippingViewRequiresAvailableShippingIntegration(): void
    {
        $Products = new ReflectionProperty(QUI\ERP\Products\Handler\Products::class, 'list');
        $originalProducts = $Products->getValue();
        $Product = $this->createMock(QUI\ERP\Products\Product\Types\AbstractType::class);
        $Products->setValue(null, [1003 => $Product]);

        $Packages = $this->createMock(PackageManager::class);
        $Packages->expects($this->once())
            ->method('isInstalled')
            ->with('quiqqer/shipping')
            ->willReturn(false);
        QUI::$PackageManager = $Packages;

        try {
            self::assertFalse(Utils::getShippingTimeFrontendView(1003));
        } finally {
            $Products->setValue(null, $originalProducts);
        }
    }
}
