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
 * An iterator to import "UnicodeNormalizationTest.X.Y.Z.txt" files from www.unicode.org.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UnicodeNormalizationTestUpdater implements \IteratorAggregate
{
    public static function setup()
    {
        if (!extension_loaded('iconv')) {
            die('Missing "iconv" extension.' . chr(10));
        }

        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');
        if ($mac !== @iconv('utf-8', 'utf-8-mac', $nfc) || $nfc !== @iconv('utf-8-mac', 'utf-8', $mac)) {
            die('Loadded "iconv" extension is not able to handle NFD_MAC.' . chr(10));
        }
    }

    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * @var string
     */
    public $source;

    /**
     * Constructor
     *
     * @param $unicodeVersion string
     */
    public function __construct($unicodeVersion)
    {
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
     * @param  int        $lineNumber
     * @param  string     $line
     * @throws \Exception
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

                if ($index === 2) {
                    $mac = array_map(
                        function ($n) use ($lineNumber) {
                            $m = iconv('utf-8', 'utf-8-mac', $n);
                            if ($m === false) {
                                throw new \Exception(
                                    sprintf(
                                        'Could not create NFD_MAC in line %s of: %s' . chr(10),
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

            return array(implode('#', array_merge(array($codes), $codesAndComment)), $comment);
        } else {
            return array(
                str_replace(
                    '#      source; NFC; NFD; NFKC; NFKD',
                    '#      source; NFC; NFD; NFKC; NFKD; NFD_MAC',
                    $line
                ),
                "",
            );
        }
    }
}
