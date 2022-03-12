<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing;

use FFI\Env\Runtime;
use FFI\Location\Locator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;

trait TestingTrait
{
    /**
     * @param string|null $message
     * @return void
     */
    public static function skipIfNoFFISupport(?string $message = null): void
    {
        try {
            self::assertHasFFISupport($message);
        } catch (AssertionFailedError $e) {
            if (Assert::getCount() === 1) {
                Assert::resetCount();
            }

            Assert::markTestSkipped($e->getMessage());
        }
    }

    /**
     * @param string|null $message
     * @return void
     */
    public static function incompleteIfNoFFISupport(?string $message = null): void
    {
        try {
            self::assertHasFFISupport($message);
        } catch (AssertionFailedError $e) {
            if (Assert::getCount() === 1) {
                Assert::resetCount();
            }

            Assert::markTestIncomplete($e->getMessage());
        }
    }

    /**
     * @param string|null $message
     * @return void
     */
    public static function assertHasFFISupport(?string $message = null): void
    {
        Assert::assertTrue(
            Runtime::isAvailable(),
            \ltrim("$message\nFailed asserting that FFI is available.")
        );
    }

    /**
     * @param string $binary
     * @param string|null $message
     * @return void
     */
    public static function assertBinaryExists(string $binary, ?string $message = null): void
    {
        Assert::assertTrue(
            Locator::exists($binary),
            \ltrim("$message\nFailed asserting that binary [$binary] exists.")
        );
    }

    /**
     * @param string|\Stringable $headers
     * @param string|null $message
     * @return void
     */
    public static function assertHeadersSyntaxValid(string|\Stringable $headers, ?string $message = null): void
    {
        $headers = (string)$headers;

        self::incompleteIfNoFFISupport();

        try {
            \FFI::cdef($headers);
        } catch (\FFI\Exception $e) {
            $message = \ltrim("$message\nFailed asserting that headers contains valid syntax\n");
            $message .= $e::class . ': ' . $e->getMessage();

            if ($e instanceof \FFI\ParserException) {
                $trace = ExceptionRenderer::toString($e, $headers, expectsCliOutput: true);
                Assert::fail("$message\n$trace");
            }

            Assert::assertStringStartsWith('Failed resolving C function', $e->getMessage(), $message);
        }
    }

    /**
     * @param string|\Stringable $headers
     * @param string|null $binary
     * @param string|null $message
     * @return void
     */
    public static function assertHeadersCompatibleWith(string|\Stringable $headers, ?string $binary, ?string $message = null): void
    {
        $headers = (string)$headers;

        self::incompleteIfNoFFISupport();

        if ($binary !== null) {
            self::assertBinaryExists($binary);

            if (($pathname = Locator::resolve($binary))) {
                \chdir(\dirname($pathname));
                $binary = $pathname;
            }
        }

        try {
            \FFI::cdef($headers, $binary);
        } catch (\FFI\Exception $e) {
            $message = \ltrim("$message\nFailed asserting that headers compatible with binary [$binary]\n");
            $message .= $e::class . ': ' . $e->getMessage();

            if ($e instanceof \FFI\ParserException) {
                $trace = ExceptionRenderer::toString($e, $headers, expectsCliOutput: true);
                Assert::fail("$message\n$trace");
            }

            Assert::fail($message);
        }
    }
}
