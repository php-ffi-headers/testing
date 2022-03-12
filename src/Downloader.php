<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing;

use FFI\Headers\Testing\Downloader\PharResult;
use FFI\Headers\Testing\Downloader\ResultInterface;
use PHPUnit\Framework\Assert;

class Downloader
{
    /**
     * @param non-empty-string $url
     * @param non-empty-string|null $temp
     */
    protected function __construct(
        protected readonly string $url,
        protected readonly ?string $temp = null,
    ) {
    }

    /**
     * @param non-empty-string|null $ext
     * @return non-empty-string
     */
    protected function temp(string $ext = null): string
    {
        $suffix = $ext ? ".$ext" : '.temp';

        return ($this->temp ?? \sys_get_temp_dir()) . '/'
            . \hash('md5', $this->url) . $suffix;
    }

    /**
     * @param non-empty-string|null $ext
     * @param bool $overwrite
     * @return non-empty-string
     */
    protected function download(bool $overwrite = true, string $ext = null): string
    {
        $temp = $this->temp($ext);

        \error_clear_last();

        $stream = @\fopen($this->url, 'rb');

        if ($error = \error_get_last()) {
            if (\str_contains($error['message'], 'Operation timed out')) {
                Assert::markTestIncomplete('Can not complete test: Downloading operation timed out');
            }

            if (\str_contains($error['message'], '404')) {
                Assert::markTestSkipped('Can not complete test: ' . $this->url . ' not found');
            }

            throw new \RuntimeException($error['message']);
        }

        \is_file($temp) and $overwrite and @\unlink($temp);
        \stream_copy_to_stream($stream, \fopen($temp, 'ab+'));

        return $temp;
    }

    /**
     * @param non-empty-string $url
     * @param array<string|int> $args
     * @param bool $overwrite
     * @return ResultInterface
     */
    public static function zip(string $url, array $args = [], bool $overwrite = false): ResultInterface
    {
        $archive = (new Downloader(\vsprintf($url, $args)))
            ->download($overwrite, 'zip');

        return new PharResult($archive);
    }
}
