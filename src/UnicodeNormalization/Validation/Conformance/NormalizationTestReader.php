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

namespace Sjorek\UnicodeNormalization\Validation\Conformance;

/**
 * An iterator to read "NormalizationTest.X.Y.Z.txt" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestReader implements \IteratorAggregate
{
    /**
     * @var string
     */
    public $unicodeVersion;

    /**
     * @var string
     */
    public $source;

    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Constructor.
     *
     * @param string $unicodeVersion
     * @param string $filePath
     */
    public function __construct($unicodeVersion, $filePath = null)
    {
        $this->unicodeVersion = $unicodeVersion;
        if (null === $filePath) {
            $sourceTemplate = implode(
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
            $filePath = sprintf($sourceTemplate, $this->unicodeVersion);
            if (!file_exists($filePath)) {
                throw new \RuntimeException(
                    sprintf(
                        'Path to fixtures "%s" does not exist. Please run this script from the project root.',
                        $filePath
                    )
                );
            }
        } else {
            if (!file_exists($filePath)) {
                throw new \RuntimeException(sprintf('Path to fixtures "%s" does not exist.', $filePath));
            }
        }

        if ('.gz' === strtolower(substr($filePath, -3))) {
            $filePath = 'compress.zlib://' . $filePath;
        }

        $this->source = $filePath;
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        if (isset($this->iterator)) {
            return $this->iterator;
        }

        $fileObject = new \SplFileObject($this->source, 'r', false);
        $array = iterator_to_array(
            (function () use ($fileObject) {
                foreach ($fileObject as $lineNumber => $line) {
                    yield from $this->processLine($lineNumber + 1, $line);
                }
            })(),
            true
        );
        unset($fileObject);

        return $this->iterator = new \ArrayIterator($array);
    }

    /**
     * @param int    $lineNumber
     * @param string $line
     *
     * @throws \Exception
     *
     * @return string[]
     */
    public function processLine($lineNumber, $line)
    {
        $codesAndComment = explode('#', $line);
        $codes = explode(';', array_shift($codesAndComment));
        $comment = array_pop($codesAndComment);
        $comment = explode(')', $comment);
        $comment = trim(array_pop($comment));

        if (7 !== count($codes)) {
            return;
        }

        $codes = array_map('trim', $codes);
        $codes = array_filter($codes);
        $codes = array_map('hex2bin', $codes);

        yield $lineNumber => [$comment, $codes];
    }
}
