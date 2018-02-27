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

namespace Sjorek\UnicodeNormalization\Tests\Conformance;

use Sjorek\UnicodeNormalization\NormalizationUtility;
use Sjorek\UnicodeNormalization\Tests\Utility\NormalizationTestUtility;

/**
 * An iterator to update "UnicodeNormalizationTest.X.Y.Z.txt" files from www.unicode.org.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestUpdater implements \IteratorAggregate
{
    /**
     * @var string
     */
    protected $source = null;

    /**
     * @var \Iterator
     */
    protected $iterator = null;

    /**
     * Constructor.
     *
     * @param $version string
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __construct($version)
    {
        if (!function_exists('mb_chr')) {
            throw new \RuntimeException('The extension "mbstring" or an appropriate polyfill is missing.');
        }
        if (!NormalizationUtility::isNfdMacCompatible()) {
            throw new \RuntimeException(
                'The extension "iconv" is either missing or does not support the "utf-8-mac" charset.'
            );
        }
        $source = NormalizationTestUtility::createDownloadUrl($version);
        $scheme = parse_url($source, PHP_URL_SCHEME);
        if ($scheme === false) {
            throw new \InvalidArgumentException(sprintf('Invalid url "%s" given.', $source));
        } elseif ($scheme === 'http' || $scheme === 'https') {
            $headers = @get_headers($source);
            if ($headers === false ||
                empty(
                    array_filter(
                        $headers,
                        function ($header) {
                            return strpos(strtoupper($header), '200 OK') !== false;
                        }
                    )
                )
            ) {
                throw new \InvalidArgumentException(sprintf('The source url is not available: %s', $source));
            }
        } elseif (!file_exists($source)) {
            throw new \InvalidArgumentException(sprintf('The source file does not exist: %s', $source));
        }
        $this->source = $source;
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        if ($this->iterator === null) {
            $this->iterator = new \NoRewindIterator(new \SplFileObject($this->source, 'r', false));
        }
        return (function () {
            $mbEncoding = null;
            if (MB_OVERLOAD_STRING & (int) ini_get('mbstring.func_overload')) {
                $mbEncoding = mb_internal_encoding();
                mb_internal_encoding('8bit');
            }
            foreach ($this->iterator as $lineNumber => $line) {
                $lineNumber += 1;
                yield $lineNumber => $this->processLine($lineNumber, $line);
            }
            if (null !== $mbEncoding) {
                mb_internal_encoding($mbEncoding);
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

        if (6 !== count($codes)) {
            return [
                str_replace(
                    '#      source; NFC; NFD; NFKC; NFKD',
                    '#      source; NFC; NFD; NFKC; NFKD; NFD_MAC',
                    $line
                ),
                false,
            ];
        }

        $comment = implode('#', $codesAndComment);
        $comment = explode(')', $comment);
        $comment = trim(array_pop($comment));

        $mac = null;
        foreach ($codes as $index => $string) {
            $codepoints = array_map(
                function ($codepoint) {
                    return mb_chr(hexdec($codepoint), 'utf-8');
                },
                explode(' ', $string)
            );

            // Should be NFD
            if (2 === $index) {
                $mac = implode(
                    '',
                    array_map(
                        function ($n) use ($lineNumber, $comment) {
                            $m = iconv('utf-8', 'utf-8-mac', $n);
                            if (false === $m) {
                                throw new \Exception(
                                    sprintf(
                                        'Could not create NFD_MAC for %s in line %s of: %s' . PHP_EOL,
                                        $comment,
                                        $lineNumber,
                                        $this->source
                                    )
                                );
                            }

                            return strtoupper(bin2hex($m));
                        },
                        $codepoints
                    )
                );
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

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
