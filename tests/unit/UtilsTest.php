<?php

namespace QUI\TemplateCologne\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QUI\TemplateCologne\Utils;

class UtilsTest extends TestCase
{
    public function testConvertBrickCSSClassReturnsEmptyStringForEmptyInput(): void
    {
        self::assertSame('', Utils::convertBrickCSSClass([]));
    }

    public function testConvertBrickCSSClassPrefixesEveryClass(): void
    {
        self::assertSame(
            ' brick-container__fullWidth brick-container__noSpacing',
            Utils::convertBrickCSSClass(['fullWidth', 'noSpacing'])
        );
    }
}
