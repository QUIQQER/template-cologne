<?php

namespace QUI\TemplateCologne\Tests\Unit;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Smarty;

class ProfileTemplateTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function tokens(): iterable
    {
        yield 'session token' => [str_repeat('a1', 32)];
        yield 'attribute escaping' => ['"><script>alert(1)</script>'];
    }

    #[DataProvider('tokens')]
    public function testProfileFormIncludesTokenForSubmissionWithoutHtmlInjection(string $token): void
    {
        $compileDirectory = sys_get_temp_dir() . '/cologne-profile-' . bin2hex(random_bytes(8));
        mkdir($compileDirectory);
        $Smarty = new Smarty();
        $Smarty->setCompileDir($compileDirectory);
        $Smarty->registerPlugin('function', 'locale', static fn (): string => '');
        $Smarty->registerPlugin('function', 'control', static fn (): string => '');
        $Smarty->assign([
            'Category' => new class {
                public function create(): string
                {
                    return '<input name="firstname" value="Test">';
                }
            },
            'this' => new class {
                public function getAttribute(string $name): bool
                {
                    return false;
                }
            },
            'categories' => [],
            'currentCategory' => 'user',
            'currentSetting' => 'data',
            'csrfToken' => $token
        ]);

        try {
            $html = $Smarty->fetch(
                dirname(__DIR__, 2) . '/quiqqer/frontend-users/src/QUI/FrontendUsers/Controls/Profile.html'
            );
            $Document = new DOMDocument();
            $Document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $XPath = new DOMXPath($Document);
            $tokens = $XPath->query(
                '//form[@data-name="form" and @method="post"]//input[@name="_csrf" and @data-name="csrf-token"]'
            );
            self::assertCount(1, $tokens);
            $Token = $tokens->item(0);
            self::assertInstanceOf(DOMElement::class, $Token);
            self::assertSame('hidden', $Token->getAttribute('type'));
            self::assertSame($token, $Token->getAttribute('value'));
            self::assertSame(0, $Document->getElementsByTagName('script')->length);
        } finally {
            foreach (glob($compileDirectory . '/*') ?: [] as $file) {
                unlink($file);
            }

            rmdir($compileDirectory);
        }
    }
}
