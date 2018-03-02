<?php

declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests\Helper;

use Sjorek\UnicodeNormalization\Filesystem\Filesystem;

/**
 * tweaked filesystem implementation for testing with vfsStream.
 */
class VfsFilesystem extends Filesystem
{
    /**
     * Checks if the given path is absolute.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isAbsolutePath($path)
    {
        if ('vfs://root' === substr($path, 0, 10)) {
            $path = substr($path, 10) ?: '/';
        }

        return parent::isAbsolutePath($path);
    }
}
