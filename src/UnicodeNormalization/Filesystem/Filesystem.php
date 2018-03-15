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
 * Facade to filesystem specific functionality, providing a reduced interface to what is needed.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 *
 * @todo Check if we need to implement chdir() to circumvent exceeding maximum path length
 */
class Filesystem implements FilesystemInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Constructor.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        $this->fs = new \Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::isDirectory()
     * @codeCoverageIgnore
     */
    public function isDirectory($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }

        return $this->exists($path) && is_dir($path) && !is_link($path);
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::exists()
     * @codeCoverageIgnore
     */
    public function exists($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }

        return $this->fs->exists($path) || is_link($path);
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::mkdir()
     * @codeCoverageIgnore
     */
    public function mkdir($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }
        $this->fs->mkdir($path);

        return $path;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::touch()
     * @codeCoverageIgnore
     */
    public function touch($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }
        $this->fs->touch($path);
        // TODO really use clearstatcache() here?
        clearstatcache(true, $path);

        return $path;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::remove()
     * @codeCoverageIgnore
     */
    public function remove($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }
        $this->fs->remove($path);
        // TODO really use clearstatcache() here?
        clearstatcache(true, $path);
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::traverse()
     * @codeCoverageIgnore
     */
    public function traverse($path, $parent = null)
    {
        if (null !== $parent) {
            $path = $this->concat($parent, $path);
        }

        return (function () use ($path) {
            $iterator = new \FilesystemIterator(
                $path,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME
            );
            foreach ($iterator as $path) {
                yield basename($path);
            }
        })();
    }

    /**
     * {@inheritdoc}
     *
     * @see FilesystemInterface::concat()
     * @codeCoverageIgnore
     */
    protected function concat(...$segments)
    {
        // Normalize separators on Windows
        if ('\\' === DIRECTORY_SEPARATOR) {
            $segments = array_map(function ($segment) { return strtr($segment, '\\', '/'); }, $segments);
        }

        return implode('/', array_map(function ($segment) { return rtrim($segment, '/'); }, $segments));
    }
}
