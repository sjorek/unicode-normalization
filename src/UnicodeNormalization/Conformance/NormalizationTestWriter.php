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

namespace Sjorek\UnicodeNormalization\Conformance;

/**
 * A file object to write "UnicodeNormalizationTest.X.Y.Z.txt" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestWriter extends \SplFileObject
{
    const FIRST_LINE = '# Generator : %s';
    const SECOND_LINE = '# Source    : %s';

    /**
     * @var string
     */
    public $filePath;

    /**
     * Constructor.
     *
     * @param string $unicodeVersion
     * @param string $generator
     * @param string $source
     * @param string $filePath
     */
    public function __construct($unicodeVersion, $generator, $source, $filePath = null)
    {
        if (null === $filePath) {
            $destinationTemplate = implode(
                DIRECTORY_SEPARATOR,
                [
                    __DIR__,
                    '..',
                    '..',
                    '..',
                    'tests',
                    'UnicodeNormalization',
                    'Tests',
                    'Fixtures',
                    'NormalizationTest.%s.txt.gz',
                ]
            );
            $filePath = sprintf($destinationTemplate, $unicodeVersion);

            if (!is_dir(dirname($filePath))) {
                throw new \RuntimeException(
                    sprintf(
                        'Path to fixtures "%s" does not exist. Please run this script from the project root.',
                        dirname($filePath)
                    )
                );
            }
        } else {
            if (!is_dir(dirname($filePath))) {
                throw new \RuntimeException(sprintf('Path to fixtures "%s" does not exist.', dirname($filePath)));
            }
        }

        if ('.gz' === strtolower(substr($filePath, -3))) {
            $filePath = 'compress.zlib://' . $filePath;
        }

        $this->filePath = $filePath;
        parent::__construct($this->filePath, 'w', false);
        $this->add(sprintf(self::FIRST_LINE, $generator) . chr(10));
        $this->add(sprintf(self::SECOND_LINE, $source) . chr(10));
        $this->add('# --------------------------------------------------------------------------------' . chr(10));
    }

    /**
     * @param string $line
     */
    public function add($line)
    {
        if (null === $this->fwrite($line)) {
            throw new \Exception(sprintf('Could not write "%s" file.', basename($this->filePath)));
        }
    }
}
