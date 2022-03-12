<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing\Downloader;

interface ResultInterface
{
    /**
     * @param non-empty-string $file
     * @return bool
     */
    public function exists(string $file): bool;

    /**
     * @param non-empty-string $file
     * @param non-empty-string $target
     * @return $this
     */
    public function extract(string $file, string $target): self;
}
