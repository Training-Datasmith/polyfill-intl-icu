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

use Symfony\Polyfill\Intl\Icu\Currencies;

class CurrenciesTest extends PolyfillTestCase
{
    public function testSymbol()
    {
        $this->assertSame('$', Currencies::getSymbol('USD'));
        $this->assertSame('$', Currencies::getSymbol('usd'));
        $this->assertSame('¥', Currencies::getSymbol('JPY'));
        $this->assertSame('€', Currencies::getSymbol('EUR'));
        $this->assertSame('R$', Currencies::getSymbol('BRL'));
        $this->assertNull(Currencies::getSymbol('NOPE'));
    }

    public function testFractionDigits()
    {
        $this->assertSame(0, Currencies::getFractionDigits('JPY'));
        $this->assertSame(3, Currencies::getFractionDigits('BHD'));
        $this->assertSame(2, Currencies::getFractionDigits('USD'));
        $this->assertSame(2, Currencies::getFractionDigits('NOPE'));
    }

    public function testRoundingIncrement()
    {
        $this->assertSame(0, Currencies::getRoundingIncrement('CHF'));
        $this->assertSame(0, Currencies::getRoundingIncrement('NOPE'));
    }
}
