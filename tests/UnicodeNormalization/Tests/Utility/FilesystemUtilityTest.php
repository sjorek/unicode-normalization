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

namespace Sjorek\UnicodeNormalization\Tests\Utility;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;
use Sjorek\UnicodeNormalization\Utility\FilesystemUtility;

/**
 * FilesystemUtility tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Utility\FilesystemUtility
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class FilesystemUtilityTest extends AbstractTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $vfs;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->vfs = vfsStream::setup(
            'root',
            null,
            [
                'existing-detection-folder' => [
                    FilesystemUtility::DETECTION_FOLDER => [],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function tearDown()
    {
        vfsStreamWrapper::unregister();
    }

    // /////////////////////////////////////////////////
    // Tests concerning unicode filesystem capabilities
    // /////////////////////////////////////////////////

    /**
     * @covers ::detectCapabilitiesForPath
     * @covers ::detect
     * @covers ::exists
     * @covers ::getFilesystem
     * @covers ::isDirectory
     *
     * @testWith                       [""]
     *                                 ["relative\/path"]
     *                                 ["vfs:\/\/root\/path-does-not-exist"]
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Invalid path given, which either is not a directory or does not exist: /
     * @expectedExceptionCode          1518778464
     *
     * @param mixed $path
     */
    public function testDetectCapabilitiesForPathWithInvalidPath($path)
    {
        FilesystemUtility::detectCapabilitiesForPath($path);
    }

    /**
     * A list of unsupported locales.
     *
     * @var array
     */
    const UNSUPPORTED_LOCALES = ['C', 'POSIX'];

    /**
     * A list of unsupported windows locales. On Windows the codepage 28591 refers to ISO-8859-1.
     *
     * @var array
     *
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/locale-names-languages-and-country-region-strings
     * @see https://msdn.microsoft.com/library/windows/desktop/dd317756.aspx
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/code-pages
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/reference/setlocale-wsetlocale
     */
    const UNSUPPORTED_WINDOWS_LOCALES = ['en-US.28591', 'C.28591', '.28591'];

    /**
     * @return array
     */
    public function provideTestDetectCapabilitiesForPathWithUnsupportedLocaleAndCharsetData()
    {
        $data = [
            [self::UNSUPPORTED_LOCALES, 'ASCII'],
        ];
        if ('//' === DIRECTORY_SEPARATOR) {
            $data[] = [self::UNSUPPORTED_WINDOWS_LOCALES, 'ASCII'];
        }

        return $data;
    }

    /**
     * @covers ::detectCapabilitiesForPath
     * @covers ::detect
     * @covers ::concat
     * @covers ::exists
     * @covers ::getFilesystem
     * @covers ::isDirectory
     *
     * @dataProvider provideTestDetectCapabilitiesForPathWithUnsupportedLocaleAndCharsetData
     *
     * @param mixed $locale
     * @param mixed $charset
     */
    public function testDetectCapabilitiesForPathWithUnsupportedLocaleAndCharset($locale, $charset)
    {
        $locale = $this->assertSetLocale($locale);
        $charset = $this->assertSetCharset($charset);

        $this->assertSame(
            [
                'locale' => false,
                'shell' => false,
                'unicode' => false,
            ],
            FilesystemUtility::detectCapabilitiesForPath($this->vfs->url()),
            'Do not expect any capabilities for unsupported locale.'
        );

        setlocale(LC_CTYPE, $locale);
        ini_set('default_charset', $charset);
    }

    /**
     * A list of utf-8 capable locales. On Windows the codepage 65001 refers to UTF-8.
     *
     * @var array
     *
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/locale-names-languages-and-country-region-strings
     * @see https://msdn.microsoft.com/library/windows/desktop/dd317756.aspx
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/code-pages
     * @see https://docs.microsoft.com/en-us/cpp/c-runtime-library/reference/setlocale-wsetlocale
     */
    const UTF8_LOCALES = [
        'en_US.UTF-8', 'en_US.UTF8',
        'en_US.utf-8', 'en_US.utf8',
        'en-US.UTF-8', 'en-US.UTF8',
        'en-US.utf-8', 'en-US.utf8',
        'en-US.65001', 'C.65001',
        'C.UTF-8', 'C.UTF8', ];

    /**
     * @covers ::detectCapabilitiesForPath
     * @covers ::detect
     * @covers ::concat
     * @covers ::exists
     * @covers ::getFilesystem
     * @covers ::isDirectory
     *
     * @expectedException              \Symfony\Component\Filesystem\Exception\IOException
     * @expectedExceptionMessageRegExp /^The detection folder "[^"]+" already exists in path: /
     * @expectedExceptionCode          1519131257
     */
    public function testDetectCapabilitiesForPathWithExistingDetectionFolder()
    {
        $locale = $this->assertSetLocale(static::UTF8_LOCALES);
        $charset = $this->assertSetCharset('UTF-8');

        FilesystemUtility::detectCapabilitiesForPath($this->vfs->getChild('existing-detection-folder')->url());

        setlocale(LC_CTYPE, $locale);
        ini_set('default_charset', $charset);
    }

    /**
     * @covers ::detectCapabilitiesForPath
     * @covers ::detect
     * @covers ::concat
     * @covers ::exists
     * @covers ::getFilesystem
     * @covers ::isDirectory
     * @covers ::mkdir
     *
     * @expectedException              \Symfony\Component\Filesystem\Exception\IOException
     * @expectedExceptionMessageRegExp /^Failed to create "vfs:\/\/root\/[^"]+"/
     */
    public function testDetectCapabilitiesForPathWithWriteProtection()
    {
        vfsStreamWrapper::unregister();
        $vfs = vfsStream::setup('root', 0555);

        $locale = $this->assertSetLocale(static::UTF8_LOCALES);
        $charset = $this->assertSetCharset('UTF-8');

        FilesystemUtility::detectCapabilitiesForPath($vfs->url());

        setlocale(LC_CTYPE, $locale);
        ini_set('default_charset', $charset);
    }

    /**
     * @covers ::detectCapabilitiesForPath
     * @covers ::detect
     * @covers ::getFilesystem
     * @covers ::clearCache
     * @covers ::concat
     * @covers ::exists
     * @covers ::isDirectory
     * @covers ::mkdir
     * @covers ::remove
     * @covers ::touch
     * @covers ::traverse
     */
    public function testDetectCapabilitiesForPath()
    {
        $locale = $this->assertSetLocale(static::UTF8_LOCALES);
        $charset = $this->assertSetCharset('UTF-8');

        $this->assertSame(
            [
                'locale' => true,
                'shell' => true,
                'unicode' => [
                    0 => true,
                    NormalizerInterface::NONE => true,
                    NormalizerInterface::NFC => true,
                    NormalizerInterface::NFD => true,
                    NormalizerInterface::NFD_MAC => true,
                    NormalizerInterface::NFKD => false,
                    NormalizerInterface::NFKC => false,
                ],
            ],
            FilesystemUtility::detectCapabilitiesForPath($this->vfs->url()),
            'Expect locale- and shell- and all (supported) unicode-capabilities.'
        );
        $this->assertFalse(
            $this->vfs->hasChild(FilesystemUtility::DETECTION_FOLDER),
            'The utf8 filesystem capability detection folder should not exist anymore.'
        );

        setlocale(LC_CTYPE, $locale);
        ini_set('default_charset', $charset);
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @param array $locales
     *
     * @return string
     */
    protected function assertSetLocale(array $locales)
    {
        $current = setlocale(LC_CTYPE, 0);
        $locale = setlocale(LC_CTYPE, $locales);
        $this->assertTrue(false !== $locale, 'Locale has been set?');
        $this->assertTrue(in_array($locale, $locales, true), 'Correct locale set?');

        return $current;
    }

    /**
     * @param string $charset
     *
     * @return string
     */
    protected function assertSetCharset($charset)
    {
        $current = ini_get('default_charset');
        $charset = ini_set('default_charset', 'UTF-8');
        $this->assertTrue(false !== $charset, 'Default charset has been set?');
        $this->assertSame($charset, ini_get('default_charset'), 'Correct default charset set?');

        return $current;
    }
}
