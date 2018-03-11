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

use Sjorek\UnicodeNormalization\Exception\FeatureDetectionFailure;
use Sjorek\UnicodeNormalization\Exception\InvalidFormFailure;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;
use Sjorek\UnicodeNormalization\Tests\Helper\ConfigurationHandler;
use Sjorek\UnicodeNormalization\Tests\Helper\NormalizationTestHandler;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;

/**
 * NormalizationUtility tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Utility\NormalizationUtility
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationUtilityTest extends AbstractTestCase
{
    // ///////////////////////////////////////////////////
    // Tests concerning normalization form string parsing
    // ///////////////////////////////////////////////////

    public function provideTestParseFormData()
    {
        $data = [];
        $matches = null;

        $reflector = new \ReflectionMethod(NormalizationUtility::class, 'parseForm');
        $docComment = $reflector->getDocComment();

        preg_match_all('/- ([^:]*) *: ([0-9]+), (.*)$/umU', $docComment, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            list(, $name, $form, $alternatives) = $match;
            $name = trim($name);

            $caption = sprintf('%s - parse as string \'%s\'', $name, $form);
            $data[$caption] = [(int) $form, (string) $form];

            $caption = sprintf('%s - parse as integer %s', $name, $form);
            $data[$caption] = [(int) $form, (int) $form];

            $alternatives = array_map('trim', explode(',', $alternatives));
            foreach ($alternatives as $alternative) {
                switch (strtolower($alternative)) {
                    case 'empty':
                        $caption = sprintf('%s - parse as empty string', $name);
                        $data[$caption] = [(int) $form, ''];
                        // continue with inner foreach loop
                        continue 2;
                    case 'null':
                        $caption = sprintf('%s - parse as null', $name);
                        $data[$caption] = [(int) $form, null];
                        break;
                    case 'true':
                        $caption = sprintf('%s - parse as boolean true', $name);
                        $data[$caption] = [(int) $form, true];
                        break;
                    case 'false':
                        $caption = sprintf('%s - parse as boolean false', $name);
                        $data[$caption] = [(int) $form, false];
                        break;
                }
                $caption = sprintf('%s - parse as string \'%s\'', $name, $alternative);
                $data[$caption] = [(int) $form, (string) $alternative];
            }
        }

        return $data;
    }

    /**
     * @covers ::parseForm()
     * @dataProvider provideTestParseFormData
     *
     * @param int   $expected
     * @param mixed $form
     */
    public function testParseForm($expected, $form)
    {
        $this->assertSame($expected, NormalizationUtility::parseForm($form));
    }

    /**
     * @covers ::parseForm()
     */
    public function testParseFormThrowsInvalidFormFailure()
    {
        $this->expectException(InvalidFormFailure::class);
        $this->expectExceptionMessage('Invalid unicode normalization form value: nonsense');
        $this->expectExceptionCode(1398603947);
        NormalizationUtility::parseForm('nonsense');
    }

    // ///////////////////////////////////////////////////
    // Tests concerning unicode capabilities
    // ///////////////////////////////////////////////////

    /**
     * @covers ::detectUnicodeVersion()
     */
    public function testDetectUnicodeVersion()
    {
        $unicodeVersion = NormalizationUtility::detectUnicodeVersion();
        $this->assertSame(1, preg_match('/^[1-9][0-9]*\.[0-9]+\.[0-9]+$/', $unicodeVersion));
        $this->assertTrue(version_compare('0.0.0', $unicodeVersion, '<'));
        if (ConfigurationHandler::isPolyfillImplementation()) {
            $this->assertSame('7.0.0', $unicodeVersion);
        }
        $latestVersion = NormalizationTestHandler::UPDATE_CHECK_VERSION_LATEST;
        $this->assertTrue(version_compare($unicodeVersion, $latestVersion, '<='));
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDetectUnicodeVersionByIntlCharGetUnicodeVersionMethod()
    {
        if (!class_exists('IntlChar', true) || !method_exists('IntlChar', 'getUnicodeVersion')) {
            $this->markTestSkipped('Skipped test as "IntlChar::getUnicodeVersion" method is not available.');
        }
        $this->testDetectUnicodeVersion();
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDetectUnicodeVersionByIntlIcuVersionConstant()
    {
        if (!defined('INTL_ICU_VERSION')) {
            define('INTL_ICU_VERSION', '60.0');
        }
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture1.php';
        $this->testDetectUnicodeVersion();
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDetectUnicodeVersionByParsingIntlExtensionInfo()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('Skipped test as "intl"-extension is not loaded.');
        }
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture1.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture2.php';
        $this->testDetectUnicodeVersion();
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDetectUnicodeVersionByParsingIntlExtensionInfoWithFailure()
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('Skipped test as "intl"-extension is not loaded.');
        }
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture1.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture2.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture3.php';
        $this->testDetectUnicodeVersion();
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @testWith ["", "empty version"]
     *           ["0.0", "unknown version"]
     *           ["x.y", "invalid version"]
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param mixed $version
     */
    public function testDetectUnicodeVersionThrowsFeatureDetectionFailureForInvalidIcuVersion($version)
    {
        if (!extension_loaded('intl')) {
            $this->markTestSkipped('Skipped test as "intl"-extension is not loaded.');
        }
        define('FAKE_INTL_ICU_VERSION', sprintf('ICU version => %s', $version));
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture1.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture2.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture4.php';
        $this->expectFeatureDetectionFailure('Could not determine unicode version from ICU version.', 1519488536);
        NormalizationUtility::detectUnicodeVersion();
    }

    /**
     * @covers ::detectUnicodeVersion()
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDetectUnicodeVersionThrowsFeatureDetectionFailureIfImplementationisMissing()
    {
        define('FAKE_INTL_ICU_VERSION', '');
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture1.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture2.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture4.php';
        require_once __DIR__ . '/../Fixtures/NormalizationUtilityTestFixture5.php';
        $this->expectFeatureDetectionFailure('Could not determine unicode version.', 1519488534);
        NormalizationUtility::detectUnicodeVersion();
    }

    /**
     * @param string $message
     * @param int    $code
     */
    protected function expectFeatureDetectionFailure($message, $code)
    {
        $this->expectException(FeatureDetectionFailure::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);
    }
}
