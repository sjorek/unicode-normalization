<?php

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests;

/**
 * An iterator to read "UnicodeNormalizationTest.X.Y.Z.txt.gz" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UnicodeNormalizationTestReader implements \IteratorAggregate
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
     * @var \SplFileObject
     */
    protected $fileObject;

    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Constructor
     *
     * @param $unicodeVersion string
     */
    public function __construct($unicodeVersion)
    {
        $this->unicodeVersion = $unicodeVersion;

        $sourceTemplate = implode(
            DIRECTORY_SEPARATOR,
            array(__DIR__, 'Fixtures', 'UnicodeNormalizationTest.%s.txt.gz')
        );
        $this->source = sprintf($sourceTemplate, $this->unicodeVersion);

        $this->fileObject = new \SplFileObject('compress.zlib://' . $this->source, 'r', false);
        $array = iterator_to_array(
            (function () {
                foreach ($this->fileObject as $lineNumber => $line) {
                    $lineNumber += 1;
                    yield from $this->processLine($lineNumber, $line);
                }
            })(),
            true
        );
        unset($this->fileObject);
        $this->iterator = new \ArrayIterator($array);
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @param  int        $lineNumber
     * @param  string     $line
     * @throws \Exception
     * @return string[]
     */
    public function processLine($lineNumber, $line)
    {
        $codesAndComment = explode('#', $line);
        $codes = explode(';', array_shift($codesAndComment));
        $comment = array_pop($codesAndComment);
        $comment = explode(')', $comment);
        $comment = trim(array_pop($comment));

        if (count($codes) !== 7) {
            return;
        }

        $codes = array_map('trim', $codes);
        $codes = array_filter($codes);
        $codes = array_map('hex2bin', $codes);

        yield $lineNumber => array($comment, $codes);
    }
}
