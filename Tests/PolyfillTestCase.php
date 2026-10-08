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

use PHPUnit\Framework\TestCase;
use Symfony\Polyfill\Intl\Icu\Icu;

abstract class PolyfillTestCase extends TestCase
{
    protected function setUp(): void
    {
        Icu::setError(Icu::U_ZERO_ERROR);
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        Icu::setError(Icu::U_ZERO_ERROR);
        date_default_timezone_set('UTC');
    }

    /**
     * @param callable $callback
     */
    protected function captureUserWarning($callback): array
    {
        $warnings = [];
        $previous = set_error_handler(function ($severity, $message) use (&$warnings) {
            if (\E_USER_WARNING === $severity) {
                $warnings[] = $message;

                return true;
            }

            return false;
        });

        try {
            $result = $callback();
        } finally {
            restore_error_handler();
            if (null !== $previous) {
                set_error_handler($previous);
            }
        }

        return [$result, $warnings];
    }

    /**
     * @param callable $callback
     */
    protected function captureUserDeprecated($callback): array
    {
        $messages = [];
        $previous = set_error_handler(function ($severity, $message) use (&$messages) {
            if (\E_USER_DEPRECATED === $severity) {
                $messages[] = $message;

                return true;
            }

            return false;
        });

        try {
            $result = $callback();
        } finally {
            restore_error_handler();
            if (null !== $previous) {
                set_error_handler($previous);
            }
        }

        return [$result, $messages];
    }
}
