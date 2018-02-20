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
 */
class Filesystem implements FilesystemInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $fs
     */
    public function __construct(\Symfony\Component\Filesystem\Filesystem $fs = null)
    {
        if (null === $fs) {
            $fs = new \Symfony\Component\Filesystem\Filesystem();
        }
        $this->fs = $fs;
    }

    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return bool
     */
    public function isAbsolutePath($file)
    {
        return $this->fs->isAbsolutePath($file);
    }

    /**
     * Creates a directory recursively.
     *
     * @param string $dir The directory path
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface On any directory creation failure
     */
    public function mkdir($dir)
    {
        $this->fs->mkdir($dir);
    }

    /**
     * Create an empty file.
     *
     * @param string $file A filename
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When touch fails
     */
    public function touch($file)
    {
        $this->fs->touch($file);
    }

    /**
     * Removes file.
     *
     * @param string $file A filename to remove
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOExceptionInterface When removal fails
     */
    public function remove($file)
    {
        $this->fs->remove($file);
    }
}
