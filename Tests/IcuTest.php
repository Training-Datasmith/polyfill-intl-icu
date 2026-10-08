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

use Symfony\Polyfill\Intl\Icu\Icu;

class IcuTest extends PolyfillTestCase
{
    public function testKnownErrors()
    {
        $this->assertSame('U_ZERO_ERROR', Icu::getErrorName(0));
        $this->assertSame('U_ILLEGAL_ARGUMENT_ERROR', Icu::getErrorName(1));
        $this->assertSame('U_PARSE_ERROR', Icu::getErrorName(9));

        $this->assertFalse(Icu::isFailure(0));
        $this->assertTrue(Icu::isFailure(1));
        $this->assertTrue(Icu::isFailure(9));
        $this->assertFalse(Icu::isFailure(-1));
    }

    public function testIsFailureUnknownPositiveCode()
    {
        $this->assertTrue(Icu::isFailure(2));
    }

    public function testSetErrorRejectsUnknownCode()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('2');

        Icu::setError(2);
    }

    public function testSetErrorRoundTrip()
    {
        Icu::setError(Icu::U_PARSE_ERROR, 'Number parsing failed');
        $this->assertSame(9, Icu::getErrorCode());
        $this->assertSame('Number parsing failed: U_PARSE_ERROR', Icu::getErrorMessage());

        Icu::setError(Icu::U_ZERO_ERROR);
        $this->assertSame(0, Icu::getErrorCode());
        $this->assertSame('U_ZERO_ERROR', Icu::getErrorMessage());
    }
}
