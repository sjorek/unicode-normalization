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

namespace Sjorek\UnicodeNormalization\Tests;

use Sjorek\UnicodeNormalization\Conformance\NormalizationTestReader;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\NormalizationUtility;
use Sjorek\UnicodeNormalization\StreamFilter;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilterTest extends AbstractNormalizationTestCase
{
    /**
     * @var string
     */
    const IMPLEMENTATION_CLASS = NormalizationUtility::IMPLEMENTATION_INTL;

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::getCodePointSize() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckGetCodePointSizeData()
    {
        // a (single byte) + ä (double byte) + € (triple byte) + 𐍈 (quad byte)
        $string = hex2bin('61c3a4e282acf0908d88');

        return [
            'single byte' => [1, $string[0]],
            'double byte opener' => [2, $string[1]],
            'double byte payload 1' => [0, $string[2]],
            'triple byte opener' => [3, $string[3]],
            'triple byte payload 1' => [0, $string[4]],
            'triple byte payload 2' => [0, $string[5]],
            'quad byte opener' => [4, $string[6]],
            'quad byte payload 1' => [0, $string[7]],
            'quad byte payload 2' => [0, $string[8]],
            'quad byte payload 3' => [0, $string[9]],
        ];
    }

    /**
     * @test
     * @dataProvider provideCheckGetCodePointSizeData
     *
     * @param int    $expected
     * @param string $byte
     */
    public function checkGetCodePointSize($expected, $byte)
    {
        $this->assertSame(
            $expected,
            $this->callProtectedMethod(StreamFilter::class, 'getCodePointSize', [$byte])
        );
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::processStringFragment() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckProcessStringFragmentData()
    {
        // déjà 훈쇼™⒜你
        $s_nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $s_nfd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');
        $s_nfkc = hex2bin('64c3a96ac3a020ed9b88ec87bc544d286129e4bda0');
        $s_nfkd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ad544d286129e4bda0');
        $s_mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        // a (single byte) + ä (double byte) + € (triple byte) + 𐍈 (quad byte)
        $string = hex2bin('61c3a4e282acf0908d88');

        $l_nfc = strlen($s_nfc);
        $l_nfd = strlen($s_nfd);
        $l_nfkc = strlen($s_nfkc);
        $l_nfkd = strlen($s_nfkd);
        $l_mac = strlen($s_mac);

        $data = [
            'return false on zero length string' => [
                false, NormalizerInterface::NONE, '', 1,
            ],
            'return false on zero length value' => [
                false, NormalizerInterface::NONE, 'x', 0,
            ],
            'return false on on invalid initial byte' => [
                false, NormalizerInterface::NONE, substr('ä', 1) . 'a', 1,
            ],
            'pass through' => [
                ['äa', 2], NormalizerInterface::NONE, 'äa', 2,
            ],
            'normalize NFC to NFC' => [
                [$s_nfc, $l_nfc], NormalizerInterface::NFC, $s_nfc, $l_nfc,
            ],
            'normalize NFD to NFC' => [
                [$s_nfc, $l_nfd], NormalizerInterface::NFC, $s_nfd, $l_nfd,
            ],
            'normalize NFKC to NFC' => [
                [$s_nfkc, $l_nfkc], NormalizerInterface::NFC, $s_nfkc, $l_nfkc,
            ],
            'normalize NFKD to NFC' => [
                [$s_nfkc, $l_nfkd], NormalizerInterface::NFC, $s_nfkd, $l_nfkd,
            ],
            'normalize NFC to NFD_MAC' => [
                [$s_mac, $l_nfc], NormalizerInterface::NFD_MAC, $s_nfc, $l_nfc,
            ],
            'normalize NFD_MAC to NFC' => [
                [$s_nfc, $l_mac], NormalizerInterface::NFC, $s_mac, $l_mac,
            ],
            'process single byte' => [
                [substr($string, 0, 1), 1], NormalizerInterface::NONE, substr($string, 0, 1), 1,
            ],
            'process partial double byte' => [
                [substr($string, 0, 1), 1], NormalizerInterface::NONE, substr($string, 0, 2), 2,
            ],
            'process double byte' => [
                [substr($string, 0, 3), 3], NormalizerInterface::NONE, substr($string, 0, 3), 3,
            ],
            'process partial triple byte' => [
                [substr($string, 0, 3), 3], NormalizerInterface::NONE, substr($string, 0, 4), 4,
            ],
            'process partial triple byte with one trailing payload byte' => [
                [substr($string, 0, 3), 3], NormalizerInterface::NONE, substr($string, 0, 5), 5,
            ],
            'process triple byte' => [
                [substr($string, 0, 6), 6], NormalizerInterface::NONE, substr($string, 0, 6), 6,
            ],
            'process partial quad byte' => [
                [substr($string, 0, 6), 6], NormalizerInterface::NONE, substr($string, 0, 7), 7,
            ],
            'process partial quad byte with one trailing payload byte' => [
                [substr($string, 0, 6), 6], NormalizerInterface::NONE, substr($string, 0, 8), 8,
            ],
            'process partial quad byte with two trailing payload bytes' => [
                [substr($string, 0, 6), 6], NormalizerInterface::NONE, substr($string, 0, 9), 9,
            ],
            'process quad byte' => [
                [substr($string, 0, 10), 10], NormalizerInterface::NONE, $string, 10,
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider provideCheckProcessStringFragmentData
     *
     * @param array|false $expected
     * @param int         $form
     * @param string      $fragment
     * @param int         $size
     */
    public function checkProcessStringFragment($expected, $form, $fragment, $size)
    {
        $this->markTestSkippedIfAppleIconvIsNotAvailable($form);
        $filter = new StreamFilter();
        $this->setProtectedProperty($filter, 'form', $form);
        $actual = $this->callProtectedMethod($filter, 'processStringFragment', [$fragment, $size, $this->subject]);
        if (false === $expected) {
            $this->assertFalse($actual);
        } else {
            $this->assertSame($expected, $actual);
        }
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::register() method tests
    // ////////////////////////////////////////////////////////////////

    /**
     * @test
     * @runInSeparateProcess
     */
    public function checkRegister()
    {
        $this->assertTrue(StreamFilter::register(), 'first stream-filter registration succeeds');
        $this->assertFalse(StreamFilter::register(), 'subsequent stream-filter registrations fail');
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::onCreate() method tests
    // ////////////////////////////////////////////////////////////////

    /**
     * @test
     * @runInSeparateProcess
     * @testWith    [1, "with normalization form constant value"]
     *              ["none", "with normalization form expression"]
     *
     * @param mixed $form
     */
    public function checkOnCreate($form)
    {
        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        $filter = stream_filter_append($stream, 'convert.unicode-normalization', STREAM_FILTER_READ, $form);
        $this->assertFalse(false === $filter, 'create stream-filter with parameter succeeds');

        $filter = stream_filter_append($stream, sprintf('convert.unicode-normalization.%s', $form));
        $this->assertFalse(false === $filter, 'create stream-filter with namespace succeeds');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @expectedException           \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @expectedExceptionMessage    Invalid unicode normalization form value: nonexistent
     * @expectedExceptionCode       1398603947
     */
    public function checkOnCreateWithInvalidParameterThrowsException()
    {
        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        // throws Exception
        stream_filter_append($stream, 'convert.unicode-normalization', STREAM_FILTER_READ, 'nonexistent');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @expectedException           \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @expectedExceptionMessage    Invalid unicode normalization form value: nonexistent
     * @expectedExceptionCode       1398603947
     */
    public function checkOnCreateWithNamespaceThrowsException()
    {
        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        // throws Exception
        stream_filter_append($stream, 'convert.unicode-normalization.nonexistent');
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::filter() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckFilterWithParameterData()
    {
        return array_map(
            function ($arguments) {
                list($expected, $form, $fragment, $size) = $arguments;
                if (false === $expected) {
                    $expected = '';
                } else {
                    $expected = array_shift($expected);
                }
                if (0 === $size) {
                    $fragment = '';
                }

                return [$expected, $form, $fragment];
            },
            $this->provideCheckProcessStringFragmentData()
        );
    }

    /**
     * @test
     * @runInSeparateProcess
     * @dataProvider provideCheckFilterWithParameterData
     *
     * @param string $expected
     * @param int    $form
     * @param string $fragment
     */
    public function checkFilterWithParameter($expected, $form, $fragment)
    {
        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();
        $filter = stream_filter_append($stream, 'convert.unicode-normalization', STREAM_FILTER_READ, $form);
        $this->assertFalse(false === $filter, 'append stream-filter with parameter succeeds');
        fwrite($stream, $fragment);
        rewind($stream);
        $this->assertSame($expected, stream_get_contents($stream));
        fclose($stream);
    }

    public function provideCheckFilterWithNamespaceData()
    {
        return array_map(
            function ($arguments) {
                list($expected, $form, $fragment) = $arguments;
                switch ($form) {
                    case NormalizerInterface::NONE:
                        $form = 'none';
                        break;
                    case NormalizerInterface::NFC:
                        $form = 'nfc';
                        break;
                    case NormalizerInterface::NFD:
                        $form = 'nfd';
                        break;
                    case NormalizerInterface::NFKC:
                        $form = 'nfkc';
                        break;
                    case NormalizerInterface::NFKD:
                        $form = 'nfkd';
                        break;
                    case NormalizerInterface::NFD_MAC:
                        $form = 'mac';
                        break;
                }

                return [$expected, $form, $fragment];
            },
            $this->provideCheckFilterWithParameterData()
        );
    }

    /**
     * @test
     * @runInSeparateProcess
     * @dataProvider provideCheckFilterWithNamespaceData
     *
     * @param string $expected
     * @param int    $form
     * @param string $fragment
     */
    public function checkFilterWithNamespace($expected, $form, $fragment)
    {
        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();
        $filter = stream_filter_append(
            $stream,
            sprintf('convert.unicode-normalization.%s', $form),
            STREAM_FILTER_READ
        );
        $this->assertFalse(false === $filter, 'append stream-filter with namespace succeeds');
        fwrite($stream, $fragment);
        rewind($stream);
        $this->assertSame($expected, stream_get_contents($stream));
        fclose($stream);
    }

    /**
     * @test
     * @large
     * @runInSeparateProcess
     * @dataProvider provideConformanceTestData
     *
     * @param string                  $unicodeVersion
     * @param int                     $form
     * @param NormalizationTestReader $fileIterator
     */
    public function checkFilterConformance(
        $unicodeVersion,
        $form,
        NormalizationTestReader $fileIterator
    ) {
        $this->markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion);

        $this->markTestSkippedIfAppleIconvIsNotAvailable($form);

        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $delimiter = ' @' . chr(10) . '@ ';
        $chunkSize = 100;
        foreach ($fileIterator as $lineNumber => $data) {
            list($comment, $codes) = $data;
            $testIterator = $this->getConformanceTestIterator(
                $unicodeVersion,
                $form,
                $lineNumber,
                $comment,
                $codes
            );
            $chunkPosition = 0;
            foreach ($testIterator as $message => $data) {
                if (0 === $chunkPosition) {
                    $expectStream = $this->createStream();
                    $actualStream = $this->createStream();
                    $filter = stream_filter_append(
                        $actualStream,
                        'convert.unicode-normalization',
                        STREAM_FILTER_READ,
                        $form
                    );
                    $this->assertFalse(false === $filter, 'append stream-filter with namespace succeeds');
                }

                list($expected, $actual) = $data;

                fwrite($expectStream, sprintf('%s: %s %s', $message, $expected, $delimiter));
                fwrite($actualStream, sprintf('%s: %s %s', $message, $actual, $delimiter));

                if ($chunkPosition < $chunkSize) {
                    $chunkPosition += 1;
                } else {
                    $chunkPosition = 0;

                    rewind($expectStream);
                    $expected = stream_get_contents($expectStream);
                    fclose($expectStream);

                    rewind($actualStream);
                    $actual = stream_get_contents($actualStream);
                    fclose($actualStream);

                    $this->assertSame(explode($delimiter, $expected), explode($delimiter, $actual));
                    $this->assertSame($expected, $actual);
                }
            }

            if ($chunkPosition > 0) {
                rewind($expectStream);
                $expected = stream_get_contents($expectStream);
                fclose($expectStream);

                rewind($actualStream);
                $actual = stream_get_contents($actualStream);
                fclose($actualStream);

                $this->assertSame(explode($delimiter, $expected), explode($delimiter, $actual));
                $this->assertSame($expected, $actual);
            }
        }
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @return resource
     */
    protected function createStream()
    {
        return fopen('php://memory', 'r+');
    }
}
