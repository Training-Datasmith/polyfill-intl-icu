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

use Symfony\Polyfill\Intl\Icu\Collator as CollatorPolyfill;
use Symfony\Polyfill\Intl\Icu\Icu;
use Symfony\Polyfill\Intl\Icu\IntlDateFormatter as IntlDateFormatterPolyfill;
use Symfony\Polyfill\Intl\Icu\Locale as LocalePolyfill;
use Symfony\Polyfill\Intl\Icu\NumberFormatter as NumberFormatterPolyfill;

class BootstrapTest extends PolyfillTestCase
{
    public function testIntlExtensionIsAbsent()
    {
        $this->assertFalse(extension_loaded('intl'));
    }

    public function testGlobalFunctionsMatchIcu()
    {
        Icu::setError(Icu::U_PARSE_ERROR, 'Number parsing failed');

        $this->assertSame(9, intl_get_error_code());
        $this->assertSame('Number parsing failed: U_PARSE_ERROR', intl_get_error_message());
        $this->assertTrue(intl_is_failure(9));
        $this->assertSame('U_PARSE_ERROR', intl_error_name(9));
        $this->assertSame('[BOGUS UErrorCode]', intl_error_name(12345));
    }

    public function testStubsExtendPolyfill()
    {
        $this->assertInstanceOf(CollatorPolyfill::class, new \Collator('en'));
        $this->assertInstanceOf(NumberFormatterPolyfill::class, new \NumberFormatter('en', \NumberFormatter::DECIMAL));
        $this->assertTrue(is_subclass_of(\Locale::class, LocalePolyfill::class));
        $this->assertInstanceOf(IntlDateFormatterPolyfill::class, new \IntlDateFormatter('en', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, 'UTC'));
    }
}
