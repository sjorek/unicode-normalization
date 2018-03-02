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

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit\Framework\Error\Warning;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\StreamFilter;
use Sjorek\UnicodeNormalization\Tests\Helper\Conformance\NormalizationTestReader;

/**
 * StreamFilter tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\StreamFilter
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilterTest extends ConformanceTestCase
{
    /**
     * @var StreamFilter
     */
    protected $subject;

    /**
     * @var StreamFilter
     */
    protected $stream;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StreamFilter();
        $this->stream = $this->createStream();
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        fclose($this->stream);
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::getCodePointSize() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideTestGetCodePointSizeData()
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
     * @covers ::getCodePointSize
     * @dataProvider provideTestGetCodePointSizeData
     *
     * @param int    $expected
     * @param string $byte
     */
    public function testGetCodePointSize($expected, $byte)
    {
        $this->assertSame(
            $expected,
            $this->callProtectedMethod(StreamFilter::class, 'getCodePointSize', $byte)
        );
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::processStringFragment() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideTestProcessStringFragmentData()
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
     * @covers ::processStringFragment
     *
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::getCodePointSize
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getNormalizationForms
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::setForm
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @dataProvider provideTestProcessStringFragmentData
     *
     * @param array|false $expected
     * @param int         $form
     * @param string      $fragment
     * @param int         $size
     */
    public function testProcessStringFragment($expected, $form, $fragment, $size)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->callProtectedMethod(
            $this->subject,
            'processStringFragment',
            $fragment,
            $size,
            /* @see NormalizationTestCase::setUpNormalizationTestCase() */
            new Normalizer($form)
        );
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
     * @covers ::register
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegisterDefaultNamespace()
    {
        $this->assertTrue(StreamFilter::register(), 'first stream-filter registration succeeds');
        $this->assertFalse(StreamFilter::register(), 'subsequent stream-filter registration fails');

        $filters = stream_get_filters();
        $this->assertContains(StreamFilter::DEFAULT_NAMESPACE, $filters);
        $this->assertContains(sprintf('%s.*', StreamFilter::DEFAULT_NAMESPACE), $filters);
    }

    /**
     * @covers ::register
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegisterCustomNamespace()
    {
        $this->assertTrue(StreamFilter::register('dummy'), 'first stream-filter registration succeeds');
        $this->assertFalse(StreamFilter::register('dummy'), 'subsequent stream-filter registration fails');

        $filters = stream_get_filters();
        $this->assertContains('dummy', $filters);
        $this->assertContains('dummy.*', $filters);
    }

    /**
     * @covers ::register
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegisterInvalidNamespace()
    {
        $this->assertFalse(StreamFilter::register('dummy2.*'), 'invalid namespace registration fails');
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::onCreate() method tests
    // ////////////////////////////////////////////////////////////////

    /**
     * @covers ::onCreate
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getForm
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @testWith    [null, "without normalization form value"]
     *              [1, "with normalization form value"]
     *              ["none", "with normalization form expression"]
     *
     * @param mixed $form
     * @param mixed $message
     */
    public function testOnCreateWithFormAsParameter($form, $message)
    {
        StreamFilter::register();
        $this->assertInternalType(
            'resource',
            stream_filter_append($this->stream, StreamFilter::DEFAULT_NAMESPACE, STREAM_FILTER_ALL, $form),
            sprintf('create stream-filter %s as parameter succeeds', $message)
        );
    }

    /**
     * @covers ::onCreate
     *
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @testWith    [1, "with normalization form value"]
     *              ["none", "with normalization form expression"]
     *
     * @param mixed $form
     * @param mixed $message
     */
    public function testOnCreateWithFormAsNamespace($form, $message)
    {
        StreamFilter::register();
        $this->assertInternalType(
            'resource',
            stream_filter_append($this->stream, sprintf('%s.%s', StreamFilter::DEFAULT_NAMESPACE, $form)),
            sprintf('create stream-filter %s as namespace succeeds', $message)
        );
    }

    /**
     * @covers ::onCreate
     *
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     */
    public function testOnCreateWithInvalidParameterTriggersWarningAndFails()
    {
        StreamFilter::register();
        $this->expectException(Warning::class);
        $this->expectExceptionMessage('Invalid unicode normalization form value: nonsense');
        $this->assertFalse(
            stream_filter_append($this->stream, StreamFilter::DEFAULT_NAMESPACE, STREAM_FILTER_READ, 'nonsense')
        );
    }

    /**
     * @covers ::onCreate
     *
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     */
    public function testOnCreateWithInvalidNamespaceTriggersWarningAndFails()
    {
        StreamFilter::register();
        $this->expectException(Warning::class);
        $this->expectExceptionMessage('Invalid unicode normalization form value: nonsense');
        $this->assertFalse(
            stream_filter_append($this->stream, sprintf('%s.nonsense', StreamFilter::DEFAULT_NAMESPACE))
        );
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::filter() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideTestFilterWithParameterData()
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
            $this->provideTestProcessStringFragmentData()
        );
    }

    /**
     * @covers ::filter
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getNormalizationForms
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::getCodePointSize
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::onCreate
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::processStringFragment
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestFilterWithParameterData
     *
     * @param string $expected
     * @param int    $form
     * @param string $fragment
     */
    public function testFilterWithParameter($expected, $form, $fragment)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        StreamFilter::register();
        $filter = stream_filter_append($this->stream, StreamFilter::DEFAULT_NAMESPACE, STREAM_FILTER_READ, $form);
        $this->assertInternalType('resource', $filter, 'append stream-filter with parameter succeeds');
        fwrite($this->stream, $fragment);
        rewind($this->stream);
        $this->assertSame($expected, stream_get_contents($this->stream));
    }

    public function provideTestFilterWithNamespaceData()
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
            $this->provideTestFilterWithParameterData()
        );
    }

    /**
     * @covers ::filter
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getNormalizationForms
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::getCodePointSize
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::onCreate
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::processStringFragment
     * @uses \Sjorek\UnicodeNormalization\StreamFilter::register
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestFilterWithNamespaceData
     *
     * @param string $expected
     * @param int    $form
     * @param string $fragment
     */
    public function testFilterWithNamespace($expected, $form, $fragment)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        StreamFilter::register();
        $namespace = sprintf('%s.%s', StreamFilter::DEFAULT_NAMESPACE, $form);
        $filter = stream_filter_append($this->stream, $namespace, STREAM_FILTER_READ);
        $this->assertInternalType('resource', $filter, 'append stream-filter with namespace succeeds');
        fwrite($this->stream, $fragment);
        rewind($this->stream);
        $this->assertSame($expected, stream_get_contents($this->stream));
    }

    /**
     * @coversNothing
     */
    public function testFilterReadStream()
    {
        // déjà 훈쇼™⒜你
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $nfd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        StreamFilter::register();
        vfsStreamWrapper::register();
        $root = vfsStream::setup('root', null, ['file' => $nfc]);
        $this->assertSame(
            $nfd,
            file_get_contents(
                sprintf(
                    'php://filter/read=%s.%s/resource=%s',
                    StreamFilter::DEFAULT_NAMESPACE,
                    'nfd',
                    $root->getChild('file')->url()
                )
            )
        );
        vfsStreamWrapper::unregister();
    }

    /**
     * @coversNothing
     */
    public function testFilterWriteStream()
    {
        // déjà 훈쇼™⒜你
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $nfd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        StreamFilter::register();
        vfsStreamWrapper::register();
        $root = vfsStream::setup('root', null, ['file' => '']);
        file_put_contents(
            sprintf(
                'php://filter/write=%s.%s/resource=%s',
                StreamFilter::DEFAULT_NAMESPACE,
                'nfd',
                $root->getChild('file')->url()
            ),
            $nfc
        );
        $this->assertSame($nfd, file_get_contents($root->getChild('file')->url()));
        vfsStreamWrapper::unregister();
    }

    /**
     * @covers ::filter
     * @large
     * @group conformance
     * @dataProvider provideConformanceTestData
     *
     * @param string                  $version
     * @param int                     $form
     * @param NormalizationTestReader $fileIterator
     */
    public function testFilterConformance($version, $form, NormalizationTestReader $fileIterator)
    {
        $this->markTestSkippedIfUnicodeConformanceLevelIsInsufficient($version);
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        StreamFilter::register();
        $namespace = StreamFilter::DEFAULT_NAMESPACE;
        $delimiter = ' @' . PHP_EOL . '@ ';
        $chunkSize = 100;
        foreach ($fileIterator as $lineNumber => $data) {
            list($comment, $codes) = $data;
            $testIterator = $this->getConformanceTestIterator($version, $form, $lineNumber, $comment, $codes);
            $position = 0;
            foreach ($testIterator as $message => $data) {
                if (0 === $position) {
                    $expectStream = $this->createStream();
                    $actualStream = $this->createStream();
                    $filter = stream_filter_append($actualStream, $namespace, STREAM_FILTER_READ, $form);
                    $this->assertInternalType('resource', $filter, 'append stream-filter with namespace succeeds');
                }

                list($expected, $actual) = $data;

                fwrite($expectStream, sprintf('%s: %s %s', $message, $expected, $delimiter));
                fwrite($actualStream, sprintf('%s: %s %s', $message, $actual, $delimiter));

                if ($position < $chunkSize) {
                    $position += 1;
                    continue;
                }

                $position = 0;

                rewind($expectStream);
                $expected = stream_get_contents($expectStream);
                fclose($expectStream);

                rewind($actualStream);
                $actual = stream_get_contents($actualStream);
                fclose($actualStream);

                $this->assertSame(explode($delimiter, $expected), explode($delimiter, $actual));
                $this->assertSame($expected, $actual);
            }

            if (0 < $position) {
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
     * Keep 1MB (= 1024 * 1024 = 1048576) in memory before creating a temporary file.
     *
     * @param int $memory
     *
     * @return resource
     */
    protected function createStream($memory = 1048576)
    {
        return fopen(sprintf('php://temp/maxmemory:%u', $memory), 'r+');
    }
}
