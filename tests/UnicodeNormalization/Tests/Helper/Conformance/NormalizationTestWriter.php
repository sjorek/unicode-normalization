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

namespace Sjorek\UnicodeNormalization\Tests\Helper\Conformance;

use Sjorek\UnicodeNormalization\Tests\Helper\NormalizationTestHandler;

/**
 * A file object to write "UnicodeNormalizationTest.X.Y.Z.txt" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestWriter extends \SplFileObject
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * Constructor.
     *
     * @param string $version
     */
    public function __construct($version)
    {
        $filePath = NormalizationTestHandler::createFilePath($version);
        if (!file_exists($filePath) && !is_writable(dirname($filePath))) {
            throw new \InvalidArgumentException(
                sprintf('The target folder is not writable: %s', dirname($filePath))
            );
        }
        if (file_exists($filePath) && !is_writable($filePath)) {
            throw new \InvalidArgumentException(
                sprintf('The target file is not writable: %s', $filePath)
            );
        }
        $this->filePath = $filePath;
        parent::__construct(NormalizationTestHandler::applyGzip($filePath), 'w', false);
    }

    /**
     * @param string $line
     */
    public function add($line)
    {
        if (null === $this->fwrite($line)) {
            throw new \Exception(sprintf('Could not write to target file: %s', basename($this->filePath)));
        }
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}
