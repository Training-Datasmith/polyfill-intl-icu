<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Polyfill\Tests\Intl\Icu;

use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;

class LocaleTest extends PolyfillTestCase
{
    public function testGetDefaultIsEn()
    {
        $this->assertSame('en', \Locale::getDefault());
    }

    public function testCanonicalize()
    {
        $this->assertSame('en', \Locale::canonicalize(''));
        $this->assertSame('en', \Locale::canonicalize('.utf8'));
        $this->assertSame('fr_FR', \Locale::canonicalize('FR-fr'));
        $this->assertSame('fr_FR', \Locale::canonicalize('FR-fr.utf8'));
        $this->assertSame('uz_Latn', \Locale::canonicalize('UZ-lATN'));
        $this->assertSame('uz_Cyrl_UZ', \Locale::canonicalize('UZ-cYRL-uz'));
        $this->assertSame('123', \Locale::canonicalize('123'));
        $this->assertSame('en', \Locale::canonicalize('en'));
    }

    public function testSetDefaultEn()
    {
        $this->assertTrue(\Locale::setDefault('en'));
        $this->assertSame('en', \Locale::getDefault());
    }

    public function testSetDefaultRejectsOtherLocale()
    {
        $this->expectException(MethodNotImplementedException::class);
        \Locale::setDefault('pt_BR');
    }

    public function testUnimplementedMethodsThrow()
    {
        $methods = [
            ['acceptFromHttp', ['pt-br,en-us;q=0.7']],
            ['composeLocale', [['language' => 'pt']]],
            ['filterMatches', ['pt-BR', 'pt-BR']],
            ['getAllVariants', ['pt_BR_Latn']],
            ['getDisplayLanguage', ['pt-Latn-BR', 'en']],
            ['getDisplayName', ['pt-Latn-BR', 'en']],
            ['getDisplayRegion', ['pt-Latn-BR', 'en']],
            ['getDisplayScript', ['pt-Latn-BR', 'en']],
            ['getDisplayVariant', ['pt-Latn-BR', 'en']],
            ['getKeywords', ['pt-BR@currency=BRL']],
            ['getPrimaryLanguage', ['pt-Latn-BR']],
            ['getRegion', ['pt-Latn-BR']],
            ['getScript', ['pt-Latn-BR']],
            ['lookup', [['pt-BR'], 'pt-BR-x-priv1']],
            ['parseLocale', ['pt-Latn-BR']],
        ];

        foreach ($methods as [$method, $args]) {
            try {
                \Locale::{$method}(...$args);
                $this->fail(sprintf('Expected MethodNotImplementedException for Locale::%s()', $method));
            } catch (MethodNotImplementedException $e) {
                $this->assertStringContainsString($method, $e->getMessage());
                $this->assertStringContainsString('Please install the "intl" extension', $e->getMessage());
            }
        }
    }
}
