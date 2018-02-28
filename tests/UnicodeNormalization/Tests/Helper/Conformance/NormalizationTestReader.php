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
 * An iterator to read "NormalizationTest.X.Y.Z.txt" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestReader implements \IteratorAggregate
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var \Iterator
     */
    protected $iterator = null;

    /**
     * @param string $version
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct($version)
    {
        $filePath = NormalizationTestHandler::createFilePath($version);
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf('The source file does not exist: %s', $filePath));
        }
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException(sprintf('The source file is not readable: %s', $filePath));
        }
        $this->filePath = NormalizationTestHandler::applyGzip($filePath);
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        if (null === $this->iterator) {
            $this->iterator = new \NoRewindIterator(new \SplFileObject($this->filePath, 'r', false));
        }

        return (function () {
            foreach ($this->iterator as $lineNumber => $line) {
                yield from $this->processLine($lineNumber + 1, $line);
            }
        })();
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
