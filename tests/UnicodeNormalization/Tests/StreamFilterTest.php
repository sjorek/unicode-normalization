<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests;

use PHPUnit\Framework\TestCase;
use Sjorek\UnicodeNormalization\StreamFilter;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilterTest extends TestCase
{
    // ////////////////////////////////////////////////////////////////
    // StreamFilter::getCodePointSize() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckGetCodePointSizeData()
    {
        // a (single byte) + ä (double byte) + € (triple byte) + 𐍈 (quad byte)
        $string = hex2bin('61c3a4e282acf0908d88');

        return array(
            'single byte' => array(1, $string[0]),
            'double byte opener' => array(2, $string[1]),
            'double byte payload 1' => array(0, $string[2]),
            'triple byte opener' => array(3, $string[3]),
            'triple byte payload 1' => array(0, $string[4]),
            'triple byte payload 2' => array(0, $string[5]),
            'quad byte opener' => array(4, $string[6]),
            'quad byte payload 1' => array(0, $string[7]),
            'quad byte payload 2' => array(0, $string[8]),
            'quad byte payload 3' => array(0, $string[9]),
        );
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
            $this->callProtectedMethod(StreamFilter::class, 'getCodePointSize', array($byte))
        );
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::processStringFragment() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckProcessStringFragmentData()
    {
        $this->markTestSkippedIfNormalizerIsNotAvailable();

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

        $data = array(
            'return false on zero length string' => array(
                false, \Normalizer::NONE, '', 1,
            ),
            'return false on zero length value' => array(
                false, \Normalizer::NONE, 'x', 0,
            ),
            'return false on on invalid initial byte' => array(
                false, \Normalizer::NONE, substr('ä', 1) . 'a', 1,
            ),
            'pass through' => array(
                array('äa', 2), \Normalizer::NONE, 'äa', 2,
            ),
            'normalize NFC to NFC' => array(
                array($s_nfc, $l_nfc), \Normalizer::NFC, $s_nfc, $l_nfc,
            ),
            'normalize NFD to NFC' => array(
                array($s_nfc, $l_nfd), \Normalizer::NFC, $s_nfd, $l_nfd,
            ),
            'normalize NFKC to NFC' => array(
                array($s_nfkc, $l_nfkc), \Normalizer::NFC, $s_nfkc, $l_nfkc,
            ),
            'normalize NFKD to NFC' => array(
                array($s_nfkc, $l_nfkd), \Normalizer::NFC, $s_nfkd, $l_nfkd,
            ),
            'normalize NFD_MAC to NFC' => array(
                array($s_nfc, $l_mac), \Normalizer::NFC, $s_mac, $l_mac,
            ),
            'process single byte' => array(
                array(substr($string, 0, 1), 1), \Normalizer::NONE, substr($string, 0, 1), 1,
            ),
            'process partial double byte' => array(
                array(substr($string, 0, 1), 1), \Normalizer::NONE, substr($string, 0, 2), 2,
            ),
            'process double byte' => array(
                array(substr($string, 0, 3), 3), \Normalizer::NONE, substr($string, 0, 3), 3,
            ),
            'process partial triple byte' => array(
                array(substr($string, 0, 3), 3), \Normalizer::NONE, substr($string, 0, 4), 4,
            ),
            'process partial triple byte with one trailing payload byte' => array(
                array(substr($string, 0, 3), 3), \Normalizer::NONE, substr($string, 0, 5), 5,
            ),
            'process triple byte' => array(
                array(substr($string, 0, 6), 6), \Normalizer::NONE, substr($string, 0, 6), 6,
            ),
            'process partial quad byte' => array(
                array(substr($string, 0, 6), 6), \Normalizer::NONE, substr($string, 0, 7), 7,
            ),
            'process partial quad byte with one trailing payload byte' => array(
                array(substr($string, 0, 6), 6), \Normalizer::NONE, substr($string, 0, 8), 8,
            ),
            'process partial quad byte with two trailing payload bytes' => array(
                array(substr($string, 0, 6), 6), \Normalizer::NONE, substr($string, 0, 9), 9,
            ),
            'process quad byte' => array(
                array(substr($string, 0, 10), 10), \Normalizer::NONE, $string, 10,
            ),
        );
        if ($this->callProtectedMethod(StreamFilter::class, 'macIconvIsAvailable')) {
            $data = array_merge(
                $data,
                array(
                    'normalize NFC to NFD_MAC' => array(
                        array($s_mac, $l_nfc), StreamFilter::NFD_MAC, $s_nfc, $l_nfc,
                    ),
                )
            );
        }

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
        $filter = new StreamFilter();
        $this->setProtectedProperty($filter, 'form', $form);
        $actual = $this->callProtectedMethod($filter, 'processStringFragment', array($fragment, $size));
        if ($expected === false) {
            $this->assertFalse($actual);
        } else {
            $this->assertEquals($expected, $actual);
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
    // StreamFilter::parseNormalizationForm() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckParseNormalizationFormData()
    {
        $data = array();
        $matches = null;

        $reflector = new \ReflectionClass(StreamFilter::class);
        $docComment = $reflector->getDocComment();

        preg_match_all('/- ([^:]*) *: ([0-9]+), (.*)$/umU', $docComment, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            list(, $name, $form, $alternatives) = $match;
            $name = trim($name);

            $caption = sprintf('%s - parse as string \'%s\'', $name, $form);
            $data[$caption] = array((int) $form, (string) $form);

            $caption = sprintf('%s - parse as integer %s', $name, $form);
            $data[$caption] = array((int) $form, (int) $form);

            $alternatives = explode(',', $alternatives);
            $alternatives = array_map('trim', $alternatives);
            foreach ($alternatives as $alternative) {
                $caption = sprintf('%s - parse as string \'%s\'', $name, $alternative);
                $data[$caption] = array((int) $form, (string) $alternative);
            }
        }

        return $data;
    }

    /**
     * @test
     * @dataProvider provideCheckParseNormalizationFormData
     *
     * @param int   $expected
     * @param mixed $form
     */
    public function checkParseNormalizationForm($expected, $form)
    {
        $this->markTestSkippedIfNormalizerIsNotAvailable();
        if ((int) $expected === StreamFilter::NFD_MAC) {
            $this->markTestSkippedIfMacIconvIsNotAvailable();
        }

        $filter = new StreamFilter();
        $this->assertSame($expected, $this->callProtectedMethod($filter, 'parseNormalizationForm', array($form)));
    }

    /**
     * @test
     * @testWith    ["", "with empty normalization form expression"]
     *              ["nonexistent", "with nonexistent normalization form expression"]
     *
     * @expectedException           InvalidArgumentException
     * @expectedExceptionCode       1507772911
     * @expectedExceptionMessage    Invalid normalization form/mode given.
     *
     * @param mixed $form
     */
    public function checkParseNormalizationFormThrowsInvalidArgumentException($form)
    {
        $this->markTestSkippedIfNormalizerIsNotAvailable();

        $filter = new StreamFilter();
        $this->callProtectedMethod($filter, 'parseNormalizationForm', array($form));
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
        $this->markTestSkippedIfNormalizerIsNotAvailable();

        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        $filter = stream_filter_append($stream, 'convert.unicode-normalization', STREAM_FILTER_READ, $form);
        $this->assertFalse($filter === false, 'create stream-filter with parameter succeeds');

        $filter = stream_filter_append($stream, sprintf('convert.unicode-normalization.%s', $form));
        $this->assertFalse($filter === false, 'create stream-filter with namespace succeeds');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @testWith    ["", "with empty normalization form expression"]
     *              ["nonexistent", "with nonexistent normalization form expression"]
     *
     * @expectedException           InvalidArgumentException
     * @expectedExceptionCode       1507772911
     * @expectedExceptionMessage    Invalid normalization form/mode given.
     *
     * @param mixed $form
     */
    public function checkOnCreateWithParameterThrowsException($form)
    {
        $this->markTestSkippedIfNormalizerIsNotAvailable();

        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        // throws Exception
        stream_filter_append($stream, 'convert.unicode-normalization', STREAM_FILTER_READ, 'NONSENSE');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @testWith    ["", "with empty normalization form expression"]
     *              ["nonexistent", "with nonexistent normalization form expression"]
     *
     * @expectedException           InvalidArgumentException
     * @expectedExceptionCode       1507772911
     * @expectedExceptionMessage    Invalid normalization form/mode given.
     *
     * @param mixed $form
     */
    public function checkOnCreateWithNamespaceThrowsException($form)
    {
        $this->markTestSkippedIfNormalizerIsNotAvailable();

        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $stream = $this->createStream();

        // throws Exception
        stream_filter_append($stream, sprintf('convert.unicode-normalization.%s', $form));
    }

    // ////////////////////////////////////////////////////////////////
    // StreamFilter::filter() method tests
    // ////////////////////////////////////////////////////////////////

    public function provideCheckFilterWithParameterData()
    {
        return array_map(
            function ($arguments) {
                list($expected, $form, $fragment, $size) = $arguments;
                if ($expected === false) {
                    $expected = '';
                } else {
                    $expected = array_shift($expected);
                }
                if ($size === 0) {
                    $fragment = '';
                }

                return array($expected, $form, $fragment);
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
        $this->assertFalse($filter === false, 'append stream-filter with parameter succeeds');
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
                    case \Normalizer::NONE:
                        $form = 'none';
                        break;
                    case \Normalizer::NFC:
                        $form = 'nfc';
                        break;
                    case \Normalizer::NFD:
                        $form = 'nfd';
                        break;
                    case \Normalizer::NFKC:
                        $form = 'nfkc';
                        break;
                    case \Normalizer::NFKD:
                        $form = 'nfkd';
                        break;
                    case StreamFilter::NFD_MAC:
                        $form = 'mac';
                        break;
                }

                return array($expected, $form, $fragment);
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
        $this->assertFalse($filter === false, 'append stream-filter with namespace succeeds');
        fwrite($stream, $fragment);
        rewind($stream);
        $this->assertSame($expected, stream_get_contents($stream));
        fclose($stream);
    }

    /**
     * @return array
     */
    public function provideCheckFilterConformanceData()
    {
        static $iterators;

        $this->markTestSkippedIfNormalizerIsNotAvailable();

        $data = array();
        $forms = array(
            \Normalizer::NONE,
            \Normalizer::NFC,
            \Normalizer::NFD,
            \Normalizer::NFKC,
            \Normalizer::NFKD,
            StreamFilter::NFD_MAC,
        );

        foreach (array('6.3.0', '7.0.0', '8.0.0', '9.0.0', '10.0.0') as $unicodeVersion) {
            foreach ($forms as $form) {
                $caption = 'unicode version %s with normalization form %s (%s)';
                switch ($form) {
                    case \Normalizer::NONE:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NONE');
                        break;
                    case \Normalizer::NFC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFC');
                        break;
                    case \Normalizer::NFD:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFD');
                        break;
                    case \Normalizer::NFKC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFKC');
                        break;
                    case \Normalizer::NFKD:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFKD');
                        break;
                    case StreamFilter::NFD_MAC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFD_MAC');
                }
                if (isset($iterators[$unicodeVersion])) {
                    $reader = $iterators[$unicodeVersion];
                } else {
                    $reader = new UnicodeNormalizationTestReader($unicodeVersion);
                    $iterators[$unicodeVersion] = $reader;
                }
                $data[$caption] = array($unicodeVersion, $form, $reader);
            }
        }

        return $data;
    }

    /**
     * @test
     * @large
     * @runInSeparateProcess
     * @dataProvider provideCheckFilterConformanceData
     *
     * @param string                         $unicodeVersion
     * @param int                            $form
     * @param UnicodeNormalizationTestReader $fileIterator
     */
    public function checkFilterConformance(
        $unicodeVersion,
        $form,
        UnicodeNormalizationTestReader $fileIterator
    ) {
        $this->markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion);

        if ($form === StreamFilter::NFD_MAC) {
            $this->markTestSkippedIfMacIconvIsNotAvailable();
        }

        $this->assertTrue(StreamFilter::register(), 'stream-filter registration succeeds');
        $delimiter = ' @' . chr(10) . '@ ';
        $chunkSize = 100;
        foreach ($fileIterator as $lineNumber => $data) {
            list($comment, $codes) = $data;
            $testIterator = $this->checkFilterConformanceIterator(
                $unicodeVersion,
                $form,
                $lineNumber,
                $comment,
                $codes
            );
            $chunkPosition = 0;
            foreach ($testIterator as $message => $data) {
                if ($chunkPosition === 0) {
                    $expectStream = $this->createStream();
                    $actualStream = $this->createStream();
                    $filter = stream_filter_append(
                        $actualStream,
                        'convert.unicode-normalization',
                        STREAM_FILTER_READ,
                        $form
                    );
                    $this->assertFalse($filter === false, 'append stream-filter with namespace succeeds');
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

                    $this->assertEquals(explode($delimiter, $expected), explode($delimiter, $actual));
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

                $this->assertEquals(explode($delimiter, $expected), explode($delimiter, $actual));
                $this->assertSame($expected, $actual);
            }
        }
    }

    /**
     * @param  string     $unicodeVersion
     * @param  int        $form
     * @param  int        $lineNumber
     * @param  string     $comment
     * @param  array      $codes
     * @return \Generator
     */
    protected function checkFilterConformanceIterator(
        $unicodeVersion,
        $form,
        $lineNumber,
        $comment,
        array $codes
    ) {

        //$f_NONE = NormalizerInterface::NONE;
        $f_NFC = \Normalizer::NFC;
        $f_NFD = \Normalizer::NFD;
        $f_NFKC = \Normalizer::NFKC;
        $f_NFKD = \Normalizer::NFKD;
        $f_MAC = StreamFilter::NFD_MAC;

        $validForMac = preg_match('/^(EFBFBD)+$/', bin2hex($codes[5]));

        if ($form === $f_NFC) {
            $message = sprintf(
                'Normalize to NFC for version %s line %s codepoint %%s: %s',
                $unicodeVersion,
                $lineNumber,
                $comment
            );
            yield sprintf($message, '1 (RAW)') => array($codes[1], $codes[0]);
            yield sprintf($message, '2 (NFC)') => array($codes[1], $codes[1]);
            yield sprintf($message, '3 (NFD)') => array($codes[1], $codes[2]);
            yield sprintf($message, '4 (NFKC)') => array($codes[3], $codes[3]);
            yield sprintf($message, '5 (NFKD)') => array($codes[3], $codes[4]);
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => array($codes[1], $codes[5]);
            }
        }

        if ($form === $f_NFD) {
            $message = sprintf(
                'Normalize to NFD for version %s line %s codepoint %%s: %s',
                $unicodeVersion,
                $lineNumber,
                $comment
            );
            yield sprintf($message, '1 (RAW)') => array($codes[2], $codes[0]);
            yield sprintf($message, '2 (NFC)') => array($codes[2], $codes[1]);
            yield sprintf($message, '3 (NFD)') => array($codes[2], $codes[2]);
            yield sprintf($message, '4 (NFKC)') => array($codes[4], $codes[3]);
            yield sprintf($message, '5 (NFKD)') => array($codes[4], $codes[4]);
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => array($codes[2], $codes[5]);
            }
        }

        if ($form === $f_NFKC) {
            $message = sprintf(
                'Normalize to NFKC for version %s line %s codepoint %%s: %s',
                $unicodeVersion,
                $lineNumber,
                $comment
            );
            yield sprintf($message, '1 (RAW)') => array($codes[3], $codes[0]);
            yield sprintf($message, '2 (NFC)') => array($codes[3], $codes[1]);
            yield sprintf($message, '3 (NFD)') => array($codes[3], $codes[2]);
            yield sprintf($message, '4 (NFKC)') => array($codes[3], $codes[3]);
            yield sprintf($message, '5 (NFKD)') => array($codes[3], $codes[4]);
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => array($codes[3], $codes[5]);
            }
        }

        if ($form === $f_NFKD) {
            $message = sprintf(
                'Normalize to NFKD for version %s line %s codepoint %%s: %s',
                $unicodeVersion,
                $lineNumber,
                $comment
            );
            yield sprintf($message, '1 (RAW)') => array($codes[4], $codes[0]);
            yield sprintf($message, '2 (NFC)') => array($codes[4], $codes[1]);
            yield sprintf($message, '3 (NFD)') => array($codes[4], $codes[2]);
            yield sprintf($message, '4 (NFKC)') => array($codes[4], $codes[3]);
            yield sprintf($message, '5 (NFKD)') => array($codes[4], $codes[4]);
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => array($codes[4], $codes[5]);
            }
        }

        if ($form === $f_MAC) {
            $message = sprintf(
                'Normalize to NFD_MAC for version %s line %s codepoint %%s: %s',
                $unicodeVersion,
                $lineNumber,
                $comment
            );
            yield sprintf($message, '1 (RAW)') => array($codes[5], $codes[0]);
            yield sprintf($message, '2 (NFC)') => array($codes[5], $codes[1]);
            yield sprintf($message, '3 (NFD)') => array($codes[5], $codes[2]);

            if ($validForMac) {
                yield sprintf($message, '4 (NFKC)') => array($codes[3], $codes[3]);
                yield sprintf($message, '5 (NFKD)') => array($codes[4], $codes[4]);
                yield sprintf($message, '6 (NFD_MAC)') => array($codes[5], $codes[5]);
            }
        }
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @param  mixed  $object
     * @param  string $methodName
     * @param  array  $arguments
     * @return mixed
     */
    protected function callProtectedMethod($objectOrClass, $methodName, array $arguments = array())
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(is_object($objectOrClass) ? $objectOrClass : null, $arguments);
    }

    /**
     * @param  mixed  $object
     * @param  string $propertyName
     * @return mixed
     */
    protected function getProtectedProperty($objectOrClass, $propertyName)
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue(is_object($objectOrClass) ? $objectOrClass : null);
    }

    /**
     * @param  mixed  $object
     * @param  string $propertyName
     * @param  mixed  $value
     * @return mixed
     */
    protected function setProtectedProperty($objectOrClass, $propertyName, $value)
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue(is_object($objectOrClass) ? $objectOrClass : null, $value);
    }

    /**
     * @return resource
     */
    protected function createStream()
    {
        return fopen('php://memory', 'r+');
    }

    /**
     */
    protected function markTestSkippedIfNormalizerIsNotAvailable()
    {
        if (!$this->callProtectedMethod(StreamFilter::class, 'normalizerIsAvailable')) {
            $this->markTestSkipped(
                'Skipped test as "Sjorek\\UnicodeNormalization\\Normalizer" class is not available.'
            );
        }
    }

    /**
     */
    protected function markTestSkippedIfMacIconvIsNotAvailable()
    {
        if (!$this->callProtectedMethod(StreamFilter::class, 'macIconvIsAvailable')) {
            $this->markTestSkipped(
                'Skipped test as "iconv" extension is either not available '
                . 'or not able to handle "utf-8-mac" charset.'
            );
        }
    }

    protected static $unicodeConformanceLevel = null;

    /**
     * @param string $unicodeVersion
     */
    protected function markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion)
    {
        if (static::$unicodeConformanceLevel === null) {
            // If it is not a mac - this is a stupid assumption that every Darwin is a Mac!
            if (false === stripos(PHP_OS, 'Darwin')) {
                // The 'intl' extension's Normalizer implementation is less conform than
                // implementations from 'patchwork/utf8' or 'symfony/polyfill-intl-normalizer'
                static::$unicodeConformanceLevel = extension_loaded('intl') ? '6.3.0' : '7.0.0';
            } else {
                // On Mac OS the 'intl'-extension uses the underlying operating
                // system features, which conforms to higher levels than above!
                static::$unicodeConformanceLevel = extension_loaded('intl') ? '9.0.0' : '7.0.0';
            }
        }
        if (version_compare($unicodeVersion, static::$unicodeConformanceLevel, '>')) {
            $this->markTestSkipped(
                sprintf(
                    'Skipped test as unicode version %s is higher than the supported unicode conformance level %s.',
                    $unicodeVersion,
                    static::$unicodeConformanceLevel
                )
            );
        }
    }
}
