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

namespace Sjorek\UnicodeNormalization\Filesystem;

/**
 * Interface for filesystem specific functionality.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
interface FilesystemInterface
{
    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return bool
     */
    public function isAbsolutePath($file);

    /**
     * Creates a directory recursively.
     *
     * @param string $dir The directory path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface On any directory creation failure
     */
    public function mkdir($dir);

    /**
     * Create an empty file.
     *
     * @param string $file A filename
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When touch fails
     */
    public function touch($file);

    /**
     * Removes file.
     *
     * @param string $file A filename to remove
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When removal fails
     */
    public function remove($file);
}
