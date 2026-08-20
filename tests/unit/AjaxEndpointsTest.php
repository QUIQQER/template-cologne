<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\ERP\Currency\Currency;
use QUI\ERP\Currency\Handler as CurrencyHandler;
use QUI\Interfaces\Template\EngineInterface;
use QUI\Projects\Project;
use QUI\Projects\Site;
use QUI\Rewrite;
use ReflectionProperty;

class AjaxEndpointsTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalCallables;

    private ?Rewrite $originalRewrite;
    private ?QUI\Template $originalTemplate;
    private mixed $originalDefaultCurrency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalCallables = $this->ajaxCallables()->getValue();
        $this->originalRewrite = QUI::$Rewrite;
        $this->originalTemplate = QUI::$Template;
        $this->originalDefaultCurrency = $this->currencyDefault()->getValue();
    }

    protected function tearDown(): void
    {
        $this->ajaxCallables()->setValue(null, $this->originalCallables);
        QUI::$Rewrite = $this->originalRewrite;
        QUI::$Template = $this->originalTemplate;
        $this->currencyDefault()->setValue(null, $this->originalDefaultCurrency);

        parent::tearDown();
    }

    public function testCountLanguagesReturnsCurrentProjectLanguageCount(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('getLanguages')->willReturn(['de', 'en', 'fr']);
        $Site = $this->createMock(Site::class);
        $Site->method('getProject')->willReturn($Project);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getSite')->willReturn($Site);
        QUI::$Rewrite = $Rewrite;

        $callable = $this->endpoint(
            'countLang.php',
            'package_quiqqer_template-cologne_ajax_countLang',
            []
        );

        self::assertSame(3, $callable());
    }

    public function testCountLanguagesRequiresCurrentSite(): void
    {
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getSite')->willReturn(null);
        QUI::$Rewrite = $Rewrite;
        $callable = $this->endpoint(
            'countLang.php',
            'package_quiqqer_template-cologne_ajax_countLang',
            []
        );

        $this->expectException(QUI\Exception::class);
        $this->expectExceptionMessage('No site available.');

        $callable();
    }

    public function testLanguageListReturnsEmptyForSingleLanguageProject(): void
    {
        $Project = $this->createMock(Project::class);
        $Project->method('get')->with(17)->willReturn($this->createMock(Site::class));
        $Project->method('getLanguages')->willReturn(['en']);
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;
        $callable = $this->endpoint(
            'getLangList.php',
            'package_quiqqer_template-cologne_ajax_getLangList',
            ['flagFolderPath', 'siteId']
        );

        self::assertSame('', $callable('/flags/', 17));
    }

    public function testLanguageListRendersAssignedProjectContext(): void
    {
        $assigned = [];
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
        $Engine->method('fetch')->willReturn('<nav>language choices</nav>');
        $Template = $this->createMock(QUI\Template::class);
        $Template->method('getEngine')->willReturn($Engine);
        QUI::$Template = $Template;
        $Currency = $this->createMock(Currency::class);
        $this->currencyDefault()->setValue(null, $Currency);
        $Site = $this->createMock(Site::class);
        $Project = $this->createMock(Project::class);
        $Project->method('get')->with(23)->willReturn($Site);
        $Project->method('getLanguages')->willReturn(['en', 'de']);
        $Project->method('getLang')->willReturn('en');
        $Rewrite = $this->createMock(Rewrite::class);
        $Rewrite->method('getProject')->willReturn($Project);
        QUI::$Rewrite = $Rewrite;
        $callable = $this->endpoint(
            'getLangList.php',
            'package_quiqqer_template-cologne_ajax_getLangList',
            ['flagFolderPath', 'siteId']
        );

        self::assertSame('<nav>language choices</nav>', $callable('/flags/', 23));
        self::assertSame($Site, $assigned['Site']);
        self::assertSame('en', $assigned['projectLang']);
        self::assertSame($Currency, $assigned['DefaultCurrency']);
        self::assertSame(['en', 'de'], $assigned['langs']);
        self::assertSame('/flags/', $assigned['path']);
    }

    /**
     * @param list<string> $parameters
     */
    private function endpoint(string $file, string $name, array $parameters): callable
    {
        require dirname(__DIR__, 2) . '/ajax/' . $file;

        $callables = QUI::getAjax()::getRegisteredCallables();
        self::assertArrayHasKey($name, $callables);
        self::assertSame($parameters, $callables[$name]['params']);

        return $callables[$name]['callable'];
    }

    private function ajaxCallables(): ReflectionProperty
    {
        return new ReflectionProperty(QUI\Ajax::class, 'callables');
    }

    private function currencyDefault(): ReflectionProperty
    {
        return new ReflectionProperty(CurrencyHandler::class, 'Default');
    }
}
