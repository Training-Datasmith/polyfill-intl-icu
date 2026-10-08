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

use Symfony\Polyfill\Intl\Icu\DateFormat\TimezoneTransformer;
use Symfony\Polyfill\Intl\Icu\Exception\MethodArgumentNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\MethodArgumentValueNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\MethodNotImplementedException;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;
use Symfony\Polyfill\Intl\Icu\Icu;

class IntlDateFormatterTest extends PolyfillTestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(\IntlDateFormatter::class, new \IntlDateFormatter('en', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC'));
        $this->assertInstanceOf(\IntlDateFormatter::class, new \IntlDateFormatter(null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC'));

        try {
            new \IntlDateFormatter('pt_BR', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC');
            $this->fail('Expected locale exception');
        } catch (MethodArgumentValueNotImplementedException $e) {
        }

        try {
            new \IntlDateFormatter('en', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC', \IntlDateFormatter::TRADITIONAL);
            $this->fail('Expected calendar exception');
        } catch (MethodArgumentValueNotImplementedException $e) {
        }

        $this->assertInstanceOf(\IntlDateFormatter::class, \IntlDateFormatter::create('en', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC'));
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::LONG, \IntlDateFormatter::MEDIUM, 'UTC');
        $this->assertSame(\IntlDateFormatter::GREGORIAN, $formatter->getCalendar());
        $this->assertSame('en', $formatter->getLocale());
        $this->assertSame(\IntlDateFormatter::LONG, $formatter->getDateType());
        $this->assertSame(\IntlDateFormatter::MEDIUM, $formatter->getTimeType());
    }

    public function testNullDateAndTimeTypesDefaultToFull()
    {
        if (\PHP_VERSION_ID >= 80100) {
            list($formatter, $messages) = $this->captureUserDeprecated(function () {
                return new \IntlDateFormatter('en', null, null, 'UTC');
            });
            $this->assertNotEmpty($messages);
            $joined = implode(' ', $messages);
            $this->assertStringContainsString('dateType', $joined);
            $this->assertStringContainsString('timeType', $joined);
        } else {
            $formatter = new \IntlDateFormatter('en', null, null, 'UTC');
        }
        $this->assertSame(\IntlDateFormatter::FULL, $formatter->getDateType());
        $this->assertSame(\IntlDateFormatter::FULL, $formatter->getTimeType());
    }

    public function testDefaultPatterns()
    {
        $cases = [
            [\IntlDateFormatter::FULL, \IntlDateFormatter::NONE, 'Thursday, January 1, 1970'],
            [\IntlDateFormatter::LONG, \IntlDateFormatter::NONE, 'January 1, 1970'],
            [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, 'Jan 1, 1970'],
            [\IntlDateFormatter::SHORT, \IntlDateFormatter::NONE, '1/1/70'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::FULL, '12:00:00 AM Coordinated Universal Time'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::LONG, '12:00:00 AM UTC'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::MEDIUM, '12:00:00 AM'],
            [\IntlDateFormatter::NONE, \IntlDateFormatter::SHORT, '12:00 AM'],
            [\IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'Jan 1, 1970, 12:00 AM'],
        ];

        foreach ($cases as list($dateType, $timeType, $expected)) {
            $formatter = new \IntlDateFormatter('en', $dateType, $timeType, 'UTC');
            $this->assertSame($expected, $formatter->format(0));
        }
    }

    public function testFormatPatternLetters()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern('y-M-d');
        $this->assertSame('1970-1-1', $formatter->format(0));

        $formatter->setPattern('yyyy-MM-dd');
        $this->assertSame('1970-01-01', $formatter->format(0));

        $formatter->setPattern('yy');
        $this->assertSame('70', $formatter->format(0));

        $formatter->setPattern('yyyyy');
        $this->assertSame('01970', $formatter->format(0));

        $formatter->setPattern("''yy");
        $this->assertSame("'70", $formatter->format(0));

        $formatter->setPattern("H 'o'' clock'");
        $this->assertSame("0 o' clock", $formatter->format(0));

        $june = (new \DateTimeImmutable('1970-06-15 13:45:07', new \DateTimeZone('UTC')))->getTimestamp();

        $formatter->setPattern('MMMM');
        $this->assertSame('June', $formatter->format($june));
        $formatter->setPattern('MMM');
        $this->assertSame('Jun', $formatter->format($june));
        $formatter->setPattern('D');
        $this->assertSame('166', $formatter->format($june));
        $formatter->setPattern('EEEE');
        $this->assertSame('Monday', $formatter->format($june));
        $formatter->setPattern('EEE');
        $this->assertSame('Mon', $formatter->format($june));
        $formatter->setPattern('EEEEE');
        $this->assertSame('M', $formatter->format($june));
        $formatter->setPattern('EEEEEE');
        $this->assertSame('Mo', $formatter->format($june));

        $formatter->setPattern('h');
        $this->assertSame('12', $formatter->format(0));
        $formatter->setPattern('HH');
        $this->assertSame('00', $formatter->format(0));
        $formatter->setPattern('K');
        $this->assertSame('0', $formatter->format(0));
        $formatter->setPattern('kk');
        $this->assertSame('24', $formatter->format(0));
        $formatter->setPattern('a');
        $this->assertSame('AM', $formatter->format(0));

        $formatter->setPattern('h');
        $this->assertSame('1', $formatter->format($june));
        $formatter->setPattern('HH');
        $this->assertSame('13', $formatter->format($june));
        $formatter->setPattern('mm');
        $this->assertSame('45', $formatter->format($june));
        $formatter->setPattern('ss');
        $this->assertSame('07', $formatter->format($june));
        $formatter->setPattern('a');
        $this->assertSame('PM', $formatter->format($june));

        $formatter->setPattern('QQQQQ');
        $this->assertSame('1', $formatter->format(0));
        $formatter->setPattern('QQQQ');
        $this->assertSame('1st quarter', $formatter->format(0));

        $formatter->setPattern('y-M-d');
        $this->assertSame('1970-1-1', $formatter->format(new \DateTimeImmutable('@0')));
    }

    public function testFormatTimezone()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern('z');
        $this->assertSame('UTC', $formatter->format(0));
        $formatter->setPattern('zzzz');
        $this->assertSame('Coordinated Universal Time', $formatter->format(0));

        $formatter->setTimeZone('GMT');
        $formatter->setPattern('z');
        $this->assertSame('GMT', $formatter->format(0));
        $formatter->setPattern('zzzz');
        $this->assertSame('Greenwich Mean Time', $formatter->format(0));

        $formatter->setTimeZone('Etc/GMT-5');
        $formatter->setPattern('z');
        $this->assertSame('GMT+5', $formatter->format(0));

        $formatter->setTimeZone('Etc/Zulu');
        $formatter->setPattern('zzzz');
        $this->assertSame('Coordinated Universal Time', $formatter->format(0));

        $formatter->setTimeZone('GMT+03:00');
        $formatter->setPattern('z');
        $this->assertSame('GMT+3', $formatter->format(0));
        $formatter->setPattern('zzzz');
        $this->assertSame('GMT+03:00', $formatter->format(0));

        $formatter->setTimeZone('Pacific/Fiji');
        $formatter->setPattern('z');
        try {
            $formatter->format(0);
            $this->fail('Expected NotImplementedException');
        } catch (NotImplementedException $e) {
        }

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern('zzzz');
        try {
            $formatter->setTimeZone('GMT+00:30');
            $this->fail('Expected NotImplementedException for GMT+00:30');
        } catch (NotImplementedException $e) {
        }

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC', \IntlDateFormatter::GREGORIAN, 'Y');
        try {
            $formatter->format(0);
            $this->fail('Expected NotImplementedException for Y');
        } catch (NotImplementedException $e) {
        }
    }

    public function testConstructorUnknownTimeZoneFallsBackToDefault()
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('Europe/Paris');
        try {
            $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'Foo/Bar');
            $formatter->setPattern('yyyy');
            $this->assertSame('Europe/Paris', $formatter->getTimeZoneId());
            $this->assertSame('1970', $formatter->format(0));
        } finally {
            date_default_timezone_set($previous);
        }

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'Etc/GMT-5');
        $formatter->setTimeZone('Foo/Bar');
        $this->assertSame('Etc/GMT-5', $formatter->getTimeZoneId());
    }

    public function testFormatRejectsBadValues()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC');
        try {
            $formatter->format(['tm_sec' => 0]);
            $this->fail('Expected exception for array');
        } catch (MethodArgumentValueNotImplementedException $e) {
        }

        $this->assertFalse($formatter->format('abc'));
        $this->assertSame(Icu::U_ILLEGAL_ARGUMENT_ERROR, $formatter->getErrorCode());
        $this->assertStringContainsString('not numeric', $formatter->getErrorMessage());

        $this->assertSame('Jan 1, 1970, 12:00 AM', $formatter->format('0'));
    }

    public function testParseRoundTrip()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern('yyyy-MM-dd HH:mm:ss');
        $expected = (new \DateTimeImmutable('1970-01-01 13:45:07', new \DateTimeZone('UTC')))->getTimestamp();
        $this->assertSame($expected, $formatter->parse('1970-01-01 13:45:07'));

        $formatter->setPattern('y-MMMM-d');
        $this->assertSame((new \DateTimeImmutable('1970-01-02', new \DateTimeZone('UTC')))->getTimestamp(), $formatter->parse('1970-January-2'));

        $formatter->setPattern('y-MMM-d');
        $this->assertSame((new \DateTimeImmutable('1970-01-02', new \DateTimeZone('UTC')))->getTimestamp(), $formatter->parse('1970-Jan-2'));

        $formatter->setPattern('yy-M-d');
        $this->assertSame((new \DateTimeImmutable('1970-01-02', new \DateTimeZone('UTC')))->getTimestamp(), $formatter->parse('70-1-2'));

        $formatter->setPattern('y-M-d HH a');
        $this->assertSame(0, $formatter->parse('1970-1-1 10 AM'));
        $this->assertSame(43200, $formatter->parse('1970-1-1 10 PM'));

        $formatter->setPattern('yyyy-MM-dd HH:mm:ss zzzz');
        $this->assertSame(10800, $formatter->parse('1970-01-01 00:00:00 GMT-03:00'));
    }

    public function testParseErrors()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern('y-MMMMM-d');
        $this->assertFalse($formatter->parse('1970-J-1'));
        $this->assertSame(Icu::U_PARSE_ERROR, $formatter->getErrorCode());
        $this->assertSame('Date parsing failed: U_PARSE_ERROR', $formatter->getErrorMessage());

        $formatter->setPattern('y-M-d');
        $this->assertSame(0, $formatter->parse('1970-1-1'));
        $this->assertSame(Icu::U_ZERO_ERROR, $formatter->getErrorCode());

        $offset = 0;
        try {
            $formatter->parse('1970', $offset);
            $this->fail('Expected offset exception');
        } catch (MethodArgumentNotImplementedException $e) {
        }
    }

    public function testRelativeDates()
    {
        $utc = new \DateTimeZone('UTC');
        $today = (new \DateTimeImmutable('today', $utc))->setTime(0, 0);
        $yesterday = $today->modify('-1 day');
        $tomorrow = $today->modify('+1 day');
        $epochPlusOne = (new \DateTimeImmutable('1970-01-02', $utc));

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::RELATIVE_FULL, \IntlDateFormatter::NONE, 'UTC');
        $this->assertSame('today', $formatter->format($today->getTimestamp()));
        $this->assertSame('Friday, January 2, 1970', $formatter->format($epochPlusOne->getTimestamp()));

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::RELATIVE_FULL, \IntlDateFormatter::FULL, 'UTC');
        $formatted = str_replace(' at ', ', ', $formatter->format($today->getTimestamp()));
        $this->assertSame('today, 12:00:00 AM Coordinated Universal Time', $formatted);

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::RELATIVE_SHORT, \IntlDateFormatter::SHORT, 'UTC');
        $this->assertSame('today, 12:00 AM', $formatter->format($today->getTimestamp()));
        $this->assertSame('yesterday, 12:00 AM', $formatter->format($yesterday->getTimestamp()));
        $this->assertSame('tomorrow, 12:00 AM', $formatter->format($tomorrow->getTimestamp()));

        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::RELATIVE_FULL, \IntlDateFormatter::NONE, 'UTC', \IntlDateFormatter::GREGORIAN, 'yyyy');
        $this->assertStringStartsWith('today', $formatter->format($today->getTimestamp()));
    }

    public function testLenientAndUnimplementedApi()
    {
        $formatter = new \IntlDateFormatter('en', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, 'UTC');
        $this->assertFalse($formatter->isLenient());
        $this->assertTrue($formatter->setLenient(false));

        try {
            $formatter->setLenient(true);
            $this->fail('Expected lenient exception');
        } catch (MethodArgumentValueNotImplementedException $e) {
        }

        try {
            \IntlDateFormatter::formatObject(new \DateTime());
            $this->fail('Expected formatObject exception');
        } catch (MethodNotImplementedException $e) {
        }

        $this->assertTrue($formatter->setPattern('yyyy-MM-dd'));
        $this->assertSame('yyyy-MM-dd', $formatter->getPattern());
    }

    public function testGetEtcTimeZoneId()
    {
        $this->assertSame('Etc/GMT+3', TimezoneTransformer::getEtcTimeZoneId('GMT-03:00'));
        $this->assertSame('Etc/GMT-3', TimezoneTransformer::getEtcTimeZoneId('GMT+0300'));
        $this->assertSame('Etc/GMT', TimezoneTransformer::getEtcTimeZoneId('GMT+00:00'));

        try {
            TimezoneTransformer::getEtcTimeZoneId('GMT+00:30');
            $this->fail('Expected NotImplementedException');
        } catch (NotImplementedException $e) {
        }

        try {
            TimezoneTransformer::getEtcTimeZoneId('UTC');
            $this->fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
        }
    }
}
