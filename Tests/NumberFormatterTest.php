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

use Symfony\Polyfill\Intl\Icu\Exception\MethodArgumentNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\MethodArgumentValueNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;
use Symfony\Polyfill\Intl\Icu\Icu;

class NumberFormatterTest extends PolyfillTestCase
{
    public function testConstructorGuards()
    {
        $this->assertInstanceOf(\NumberFormatter::class, new \NumberFormatter(null, \NumberFormatter::DECIMAL));

        try {
            new \NumberFormatter('pt_BR', \NumberFormatter::DECIMAL);
            $this->fail('Expected exception for locale');
        } catch (MethodArgumentValueNotImplementedException $e) {
            $this->assertStringContainsString('pt_BR', $e->getMessage());
        }

        try {
            new \NumberFormatter('en');
            $this->fail('Expected exception for missing style');
        } catch (MethodArgumentValueNotImplementedException $e) {
            $this->assertStringContainsString('DECIMAL', $e->getMessage());
            $this->assertStringContainsString('CURRENCY', $e->getMessage());
        }

        try {
            new \NumberFormatter('en', \NumberFormatter::PATTERN_DECIMAL);
            $this->fail('Expected exception for style');
        } catch (MethodArgumentValueNotImplementedException $e) {
        }

        try {
            new \NumberFormatter('en', \NumberFormatter::DECIMAL, '');
            $this->fail('Expected exception for pattern');
        } catch (MethodArgumentNotImplementedException $e) {
        }

        $this->assertInstanceOf(\NumberFormatter::class, \NumberFormatter::create('en', \NumberFormatter::DECIMAL));
    }

    public function testFormatUsesValuePrecisionUntilFractionDigitsSet()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertSame('1.123', $formatter->format(1.123));
        $this->assertSame(0, $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS));

        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 0);
        $this->assertSame('1', $formatter->format(1.123));
        $this->assertSame(0, $formatter->getAttribute(\NumberFormatter::FRACTION_DIGITS));

        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 1);
        $this->assertSame('1.1', $formatter->format(1.123));

        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);
        $this->assertSame('1.12', $formatter->format(1.123));

        $negative = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertTrue($negative->setAttribute(\NumberFormatter::FRACTION_DIGITS, -1));
        $this->assertSame(0, $negative->getAttribute(\NumberFormatter::FRACTION_DIGITS));
        $this->assertSame('1.123', $negative->format(1.123));
    }

    public function testGrouping()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertSame('1,000', $formatter->format(1000));

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 0);
        $this->assertSame('1000', $formatter->format(1000));
        $this->assertSame(0, $formatter->getAttribute(\NumberFormatter::GROUPING_USED));

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 1);
        $this->assertSame(1, $formatter->getAttribute(\NumberFormatter::GROUPING_USED));
        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 2);
        $this->assertSame(1, $formatter->getAttribute(\NumberFormatter::GROUPING_USED));
        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, -1);
        $this->assertSame(1, $formatter->getAttribute(\NumberFormatter::GROUPING_USED));
    }

    public function testRoundingModes()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, 2);

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
        $this->assertSame('1.12', $formatter->format(1.121));
        $this->assertSame('1.13', $formatter->format(1.127));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFDOWN);
        $this->assertSame('1.12', $formatter->format(1.125));
        $this->assertSame('1.13', $formatter->format(1.127));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFEVEN);
        $this->assertSame('1.12', $formatter->format(1.125));
        $this->assertSame('1.13', $formatter->format(1.129));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_CEILING);
        $this->assertSame('1.13', $formatter->format(1.121));
        $this->assertSame('-1.12', $formatter->format(-1.121));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_FLOOR);
        $this->assertSame('1.12', $formatter->format(1.129));
        $this->assertSame('-1.13', $formatter->format(-1.121));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_DOWN);
        $this->assertSame('1.12', $formatter->format(1.129));
        $this->assertSame('-1.12', $formatter->format(-1.129));

        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_UP);
        $this->assertSame('1.13', $formatter->format(1.121));
        $this->assertSame('-1.13', $formatter->format(-1.121));

        $this->expectException(MethodArgumentValueNotImplementedException::class);
        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, -1);
    }

    public function testFormatCurrency()
    {
        $decimal = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertSame('1,000.12', $decimal->formatCurrency(1000.12, 'JPY'));

        $currency = new \NumberFormatter('en', \NumberFormatter::CURRENCY);
        $this->assertSame('¥1,000', $currency->formatCurrency(1000.12, 'JPY'));
        $this->assertSame('-¥100', $currency->formatCurrency(-100, 'JPY'));
        $this->assertSame('-€1,000.12', $currency->formatCurrency(-1000.12, 'EUR'));
        $this->assertSame("ALL\xc2\xa0100", $currency->formatCurrency(100, 'ALL'));
        $this->assertSame("BHD\xc2\xa01,000.100", $currency->formatCurrency(1000.1, 'BHD'));
        $this->assertSame('$1,000.50', $currency->formatCurrency(1000.5, 'USD'));
        $this->assertSame("CHF\xc2\xa01,000.12", $currency->formatCurrency(1000.125, 'CHF'));
        $this->assertFalse($currency->formatCurrency(1, 'NOPE'));

        $this->expectException(NotImplementedException::class);
        $currency->format(1);
    }

    public function testFormatTypeCurrencyIsRejected()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        if (\PHP_VERSION_ID >= 80000) {
            $this->expectException(\ValueError::class);
            $this->expectExceptionMessage('(4 given)');
            $formatter->format(1, \NumberFormatter::TYPE_CURRENCY);
        } else {
            list($result, $warnings) = $this->captureUserWarning(function () use ($formatter) {
                return $formatter->format(1, \NumberFormatter::TYPE_CURRENCY);
            });
            $this->assertFalse($result);
            $this->assertCount(1, $warnings);
            $this->assertStringContainsString('Unsupported format type', $warnings[0]);
        }
    }

    public function testParse()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 1);

        $position = 0;
        $this->assertSame(1.4, $formatter->parse('1.4suffix', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(3, $position);

        $position = 0;
        $this->assertSame(-123456789.66, $formatter->parse('-123,456,789.66', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(15, $position);

        $position = 0;
        $this->assertSame(239.0, $formatter->parse('239.', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(4, $position);

        $position = 0;
        $this->assertFalse($formatter->parse('prefix1', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(0, $position);
        $this->assertSame(Icu::U_PARSE_ERROR, $formatter->getErrorCode());
        $this->assertSame('Number parsing failed: U_PARSE_ERROR', $formatter->getErrorMessage());
        $this->assertTrue(Icu::isFailure(Icu::getErrorCode()));

        $position = 0;
        $this->assertFalse($formatter->parse('-123,4', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(1, $position);

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, 0);
        $position = 0;
        $this->assertSame(1.0, $formatter->parse('1,234.4suffix', \NumberFormatter::TYPE_DOUBLE, $position));
        $this->assertSame(1, $position);

        $this->assertSame('1.5', $formatter->format(1.5));
        $this->assertSame(Icu::U_ZERO_ERROR, $formatter->getErrorCode());
        $this->assertSame('U_ZERO_ERROR', $formatter->getErrorMessage());
    }

    public function testParseRejectsDefaultAndCurrencyTypes()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        if (\PHP_VERSION_ID >= 80000) {
            $this->expectException(\ValueError::class);
            $formatter->parse('1', \NumberFormatter::TYPE_DEFAULT);
        } else {
            list($result, $warnings) = $this->captureUserWarning(function () use ($formatter) {
                return $formatter->parse('1', \NumberFormatter::TYPE_DEFAULT);
            });
            $this->assertFalse($result);
            $this->assertNotEmpty($warnings);
        }
    }

    public function testParseInt32AndInt64()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertSame(2147483647, $formatter->parse('2147483647', \NumberFormatter::TYPE_INT32));
        $this->assertSame(-2147483648, $formatter->parse('-2147483648', \NumberFormatter::TYPE_INT32));
        $this->assertFalse($formatter->parse('2147483648', \NumberFormatter::TYPE_INT32));
        $this->assertFalse($formatter->parse('-2147483649', \NumberFormatter::TYPE_INT32));

        if (8 === \PHP_INT_SIZE) {
            $parsed = $formatter->parse('2147483648', \NumberFormatter::TYPE_INT64);
            $this->assertIsInt($parsed);
            $this->assertSame(2147483648, $parsed);
        }
    }

    public function testSymbolsAndTextAttributes()
    {
        $decimal = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $currency = new \NumberFormatter('en', \NumberFormatter::CURRENCY);
        $this->assertSame('.', $decimal->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL));
        $this->assertSame(',', $decimal->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL));
        $this->assertFalse($decimal->getSymbol(99));
        $this->assertSame('-', $decimal->getTextAttribute(\NumberFormatter::NEGATIVE_PREFIX));
        $this->assertSame('¤', $currency->getTextAttribute(\NumberFormatter::POSITIVE_PREFIX));
    }

    public function testUnimplementedMethodsThrow()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->expectException(MethodNotImplementedException::class);
        $formatter->getPattern();
    }

    public function testLocaleAndInitialError()
    {
        $formatter = new \NumberFormatter('en', \NumberFormatter::DECIMAL);
        $this->assertSame('en', $formatter->getLocale());
        $this->assertSame(Icu::U_ZERO_ERROR, $formatter->getErrorCode());
        $this->assertSame('U_ZERO_ERROR', $formatter->getErrorMessage());
    }
}
