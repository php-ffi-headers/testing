<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing;

use FFI\ParserException;
use SebastianBergmann\Environment\Console;

/**
 * @psalm-type ErrorVisualizeSize = positive-int|0
 * @psalm-type HeaderLine = positive-int
 */
class ExceptionRenderer
{
    /**
     * @var ErrorVisualizeSize
     */
    final public const DEFAULT_SIZE = 2;

    /**
     * @param ParserException $e
     * @return HeaderLine
     */
    protected static function getErrorLine(ParserException $e): int
    {
        \preg_match('/at line (\d+)/isum', $e->getMessage(), $matches);

        return (int)($matches[1] ?? 1);
    }

    /**
     * @param ParserException $e
     * @param string $header
     * @param ErrorVisualizeSize $size
     * @return array<HeaderLine, string>
     */
    public static function toArray(ParserException $e, string $header, int $size = self::DEFAULT_SIZE): array
    {
        return \iterator_to_array(static::toIterator($e, $header, $size));
    }

    /**
     * @param ParserException $e
     * @param string $header
     * @param ErrorVisualizeSize $size
     * @return \Traversable<HeaderLine, string>
     */
    public static function toIterator(ParserException $e, string $header, int $size = self::DEFAULT_SIZE): \Traversable
    {
        $line = static::getErrorLine($e);
        $lines = \explode("\n", $header);

        for ($current = \max(0, $line - $size), $to = $line + $size; $current <= $to; ++$current) {
            yield $current + 1 => $lines[$current] ?? '';
        }
    }

    /**
     * @param ParserException $e
     * @param string $header
     * @param ErrorVisualizeSize $size
     * @return string
     */
    public static function toString(ParserException $e, string $header, int $size = self::DEFAULT_SIZE): string
    {
        $result = [];

        foreach (static::toIterator($e, $header, $size) as $line => $text) {
            $result[] = \sprintf('%5d. | %s', $line, $text);
        }

        return \implode("\n", $result);
    }

    /**
     * @param ParserException $e
     * @param string $header
     * @param ErrorVisualizeSize $size
     * @return void
     */
    public static function dump(ParserException $e, string $header, int $size = self::DEFAULT_SIZE): void
    {
        $hasColors = \class_exists(Console::class) && (new Console())->hasColorSupport();
        $result = [];
        $error = null;

        foreach (static::toIterator($e, $header, $size) as $line => $text) {
            if ($hasColors && $line === ($error ??= static::getErrorLine($e))) {
                $result[] = \sprintf("%5d. | \u{001b}[41m\u{001b}[37;1m%s\u{001b}[0m", $line, $text);
                continue;
            }

            $result[] = \sprintf('%5d. | %s', $line, $text);
        }

        \file_put_contents('php://stdout', \implode("\n", $result));
    }
}
