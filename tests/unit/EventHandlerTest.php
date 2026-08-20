<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Products\Product\ViewFrontend;
use QUI\Projects\Project;
use QUI\Rewrite;
use QUI\Smarty\Collector;
use QUI\TemplateCologne\EventHandler;
use Smarty;

class EventHandlerTest extends TestCase
{
    private ?Rewrite $originalRewrite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRewrite = QUI::$Rewrite;
    }

    protected function tearDown(): void
    {
        QUI::$Rewrite = $this->originalRewrite;
        parent::tearDown();
    }

    public function testSmartyInitializationRegistersTemplateHelpers(): void
    {
        $Smarty = new Smarty();

        EventHandler::onSmartyInit($Smarty);

        self::assertSame(
            '\\QUI\\TemplateCologne\\Utils',
            $Smarty->registered_classes['QUI\\TemplateCologne\\Utils']
        );
        self::assertSame(
            '\\QUI\\Bricks\\Manager',
            $Smarty->registered_classes['QUI\\Bricks\\Manager']
        );
        self::assertArrayHasKey('sizeof', $Smarty->registered_plugins['modifier']);
        self::assertArrayHasKey('is_numeric', $Smarty->registered_plugins['modifier']);
    }

    public function testBuyNowButtonIsNotAddedWithoutProject(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn(null);
        QUI::$Rewrite = $Rewrite;
        $Collector = new Collector();

        EventHandler::onQuiqqerProductsProductButtonsEnd(
            $Collector,
            $this->createMock(ViewFrontend::class)
        );

        self::assertSame('', $Collector->getContent());
    }

    public function testBuyNowButtonHonorsDisabledSetting(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')
            ->with('templateCologne.settings.showBuyNowButton')
            ->willReturn(false);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;
        $Collector = new Collector();

        EventHandler::onQuiqqerProductsProductButtonsEnd(
            $Collector,
            $this->createMock(ViewFrontend::class)
        );

        self::assertSame('', $Collector->getContent());
    }

    public function testBuyNowButtonReflectsProductAvailability(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getConfig')->willReturn(true);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;

        $Available = $this->createMock(ViewFrontend::class);
        $Available->method('getMaximumQuantity')->willReturn(3.0);
        $AvailableCollector = new Collector();
        EventHandler::onQuiqqerProductsProductButtonsEnd($AvailableCollector, $Available);

        self::assertStringContainsString(
            'data-qui-options-disabled="0"',
            $AvailableCollector->getContent()
        );
        self::assertStringContainsString(
            QUI::getLocale()->get(
                'quiqqer/template-cologne',
                'control.product.buy.know.button'
            ),
            $AvailableCollector->getContent()
        );

        $Unavailable = $this->createMock(ViewFrontend::class);
        $Unavailable->method('getMaximumQuantity')->willReturn(0.0);
        $UnavailableCollector = new Collector();
        EventHandler::onQuiqqerProductsProductButtonsEnd($UnavailableCollector, $Unavailable);

        self::assertStringContainsString(
            'data-qui-options-disabled="1"',
            $UnavailableCollector->getContent()
        );
    }
}
