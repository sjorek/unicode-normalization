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

use Sjorek\UnicodeNormalization\NormalizationUtility;

/**
 * An iterator to update "UnicodeNormalizationTest.X.Y.Z.txt" files from www.unicode.org.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestUpdater implements \IteratorAggregate
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @var string
     */
    public $source;

    /**
     * Constructor.
     *
     * @param $unicodeVersion string
     *
     * @throws \RuntimeException
     */
    public function __construct($unicodeVersion)
    {
        if (!extension_loaded('mbstring')) {
            throw new \RuntimeException('The required extension "mbstring" is not loaded');
        }

        if (!NormalizationUtility::appleIconvIsAvailable()) {
            throw new \RuntimeException(
                'The required extension "iconv" is either not loaded or not able to handle NFD_MAC'
            );
        }

        $sourceTemplate = 'https://www.unicode.org/Public/%s/ucd/NormalizationTest.txt';
        $this->source = sprintf($sourceTemplate, $unicodeVersion);
        $this->iterator = new \NoRewindIterator(new \SplFileObject($this->source, 'r', false));
    }

    /**
     * @return \Closure
     */
    public function getIterator()
    {
        return (function () {
            foreach ($this->iterator as $lineNumber => $line) {
                $lineNumber += 1;
                yield $lineNumber => $this->processLine($lineNumber, $line);
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

        $comment = implode('#', $codesAndComment);
        $comment = explode(')', $comment);
        $comment = trim(array_pop($comment));

        if (6 === count($codes)) {
            $mac = null;
            foreach ($codes as $index => $string) {
                $codepoints = array_map(
                    function ($codepoint) {
                        return mb_chr(hexdec($codepoint), 'utf-8');
                    },
                    explode(' ', $string)
                );

                if (2 === $index) {
                    $mac = array_map(
                        function ($n) use ($lineNumber) {
                            $m = iconv('utf-8', 'utf-8-mac', $n);
                            if (false === $m) {
                                throw new \Exception(
                                    sprintf(
                                        'Could not create NFD_MAC in line %s of: %s' . PHP_EOL,
                                        $lineNumber,
                                        $this->source
                                    )
                                );
                            }

                            return strtoupper(bin2hex($m));
                        },
                        $codepoints
                        );
                    $mac = implode('', $mac);
                }

                $codepoints = array_map('bin2hex', $codepoints);
                $codepoints = array_map('strtoupper', $codepoints);
                $codes[$index] = implode('', $codepoints);
            }
            $codes[5] = $mac;
            $codes[6] = ' ';
            $codes = implode(';', $codes);

            return [implode('#', array_merge([$codes], $codesAndComment)), $comment];
        }

        return [
            str_replace(
                '#      source; NFC; NFD; NFKC; NFKD',
                '#      source; NFC; NFD; NFKC; NFKD; NFD_MAC',
                $line
            ),
            '',
        ];
    }
}
