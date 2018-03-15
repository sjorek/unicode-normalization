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
     * Returns whether the path points to a directory, taking symlinks into account. The latter are not considered
     * to be a valid directory.
     *
     * @param string      $path   A file or directory path
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface If path length exceeds the limit
     *
     * @return bool
     */
    public function isDirectory($path, $parent = null);

    /**
     * Returns whether the path already exists, taking dangling symlinks into account.
     *
     * @param string      $path   A file or directory path
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface If path length exceeds the limit
     *
     * @return bool
     */
    public function exists($path, $parent = null);

    /**
     * Creates a directory recursively.
     *
     * @param string      $path   The directory path
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface On any directory creation failure
     *
     * @return string The path of the directory, prepended with the given parent path
     */
    public function mkdir($path, $parent = null);

    /**
     * Create an empty file.
     *
     * @param string      $path   A filename or -path
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When touch fails
     *
     * @return string The path of the file, prepended with the given parent path
     */
    public function touch($path, $parent = null);

    /**
     * Removes file, symlinks and folders. The latter is processed recursively.
     *
     * @param string      $path   A path to remove
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When removal fails
     */
    public function remove($path, $parent = null);

    /**
     * Return a filename yielding traverse-able object or an generator for the given path,
     * for use in foreach-loops. Filenames with leading dot should be ignored.
     *
     * @param string      $path   A path to traverse upon
     * @param null|string $parent A optional path to prepend, while operating with the given path
     *
     * @return \Generator|\Traversable
     */
    public function traverse($path, $parent = null);
}
