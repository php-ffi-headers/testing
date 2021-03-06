<?php

/**
 * This file is part of FFI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FFI\Headers\Testing\Downloader;

class PharResult extends Result
{
    /**
     * @var \PharData
     */
    private \PharData $phar;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->phar = new \PharData($url);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function exists(string $file): bool
    {
        return isset($this->phar[$file]);
    }

    /**
     * @param string $file
     * @param string $target
     * @return $this
     */
    public function extract(string $file, string $target): self
    {
        if (!isset($this->phar[$file])) {
            throw new \InvalidArgumentException('File [' . $file  .'] not found');
        }

        /** @var \PharFileInfo $info */
        $info = $this->phar[$file];

        if (!\is_dir($directory = \dirname($target))) {
            \mkdir($directory, recursive: true);
        }

        \file_put_contents($target, $info->getContent());

        return $this;
    }
}
