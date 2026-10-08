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

use Symfony\Polyfill\Intl\Icu\Exception\MethodArgumentValueNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Icu;

class CollatorTest extends PolyfillTestCase
{
    public function testConstructorAcceptsEnAndNull()
    {
        $en = new \Collator('en');
        $this->assertInstanceOf(\Collator::class, $en);
        $this->assertInstanceOf(\Collator::class, new \Collator(null));
        $this->assertInstanceOf(\Collator::class, \Collator::create('en'));
        $this->assertSame('en', $en->getLocale());
        $this->assertSame('en', $en->getLocale(\Locale::VALID_LOCALE));
    }

    public function testConstructorRejectsOtherLocale()
    {
        $this->expectException(MethodArgumentValueNotImplementedException::class);
        $this->expectExceptionMessage('pt_BR');
        new \Collator('pt_BR');
    }

    public function testAsortRegularPreservesKeys()
    {
        $array = ['c', 'b', 'a'];
        $collator = new \Collator('en');
        $this->assertTrue($collator->asort($array, \Collator::SORT_REGULAR));
        $this->assertSame([2 => 'a', 1 => 'b', 0 => 'c'], $array);
    }

    public function testAsortFlagMapping()
    {
        $collator = new \Collator('en');

        $array = ['10', '2', '1'];
        $collator->asort($array, \Collator::SORT_STRING);
        $this->assertSame(['1', '10', '2'], array_values($array));

        $array = ['10', '2', '1'];
        $collator->asort($array, \Collator::SORT_NUMERIC);
        $this->assertSame(['1', '2', '10'], array_values($array));
    }

    public function testAsortUnknownFlagUsesRegular()
    {
        $array = ['b', 'a'];
        $collator = new \Collator('en');
        $this->assertTrue($collator->asort($array, 99));
        $this->assertSame(['a', 'b'], array_values($array));
    }

    public function testErrorAccessors()
    {
        $collator = new \Collator('en');
        $this->assertSame(Icu::U_ZERO_ERROR, $collator->getErrorCode());
        $this->assertSame('U_ZERO_ERROR', $collator->getErrorMessage());
    }

    public function testCompareThrows()
    {
        $this->expectException(MethodNotImplementedException::class);
        (new \Collator('en'))->compare('a', 'b');
    }

    public function testSortThrows()
    {
        $this->expectException(MethodNotImplementedException::class);
        $a = ['a'];
        (new \Collator('en'))->sort($a);
    }
}
