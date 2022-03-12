<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing\Downloader;

abstract class Result implements ResultInterface
{
    /**
     * @psalm-type CallableHandler = callable(Result):mixed|void
     *
     * @param string $file
     * @param CallableHandler|null $then
     * @param CallableHandler|null $otherwise
     * @return $this
     */
    public function whenExists(string $file, callable $then = null, callable $otherwise = null): self
    {
        if ($this->exists($file)) {
            $then && $then($this);
        } else {
            $otherwise && $otherwise($this);
        }

        return $this;
    }

    /**
     * @param non-empty-string $file
     * @param non-empty-string $target
     * @return $this
     */
    public function extractIfExists(string $file, string $target): self
    {
        if ($this->exists($file)) {
            return $this->extract($file, $target);
        }

        return $this;
    }
}
