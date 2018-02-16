<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Helper;


use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Facade to filesystem specific functionality, providing a reduced interface to what is needed.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class Filesystem extends \Symfony\Component\Filesystem\Filesystem implements FilesystemInterface
{
    /**
     * Returns whether the file path is an absolute path.
     *
     * @param string $file A file path
     *
     * @return bool
     */
    public function isAbsolutePath($file)
    {
        return parent::isAbsolutePath($file);
    }

    /**
     * Creates a directory recursively.
     *
     * @param string $dir The directory path
     *
     * @throws IOException On any directory creation failure
     */
    public function mkdir($dir)
    {
        parent::mkdir($dir);
    }

    /**
     * Create an empty file.
     *
     * @param string $file A filename
     *
     * @throws IOException When touch fails
     */
    public function touch($file)
    {
        parent::touch($file);
    }

    /**
     * Removes file.
     *
     * @param string $file A filename to remove
     *
     * @throws IOException When removal fails
     */
    public function remove($file)
    {
        parent::remove($file);
    }
}
