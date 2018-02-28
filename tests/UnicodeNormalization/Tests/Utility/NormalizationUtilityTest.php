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

use Sjorek\UnicodeNormalization\Implementation\MissingNormalizer;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;
use Sjorek\UnicodeNormalization\Tests\Helper\ConfigurationHandler;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;

/**
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
     * @dataProvider provideTestParseFormData
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::parseForm()
     *
     * @param int   $expected
     * @param mixed $form
     */
    public function testParseForm($expected, $form)
    {
        $this->assertSame($expected, NormalizationUtility::parseForm($form));
    }

    /**
     * @expectedException           \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @expectedExceptionMessage    Invalid unicode normalization form value: nonsense
     * @expectedExceptionCode       1398603947
     * @covers \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::parseForm()
     */
    public function testParseFormThrowsInvalidNormalizationFormException()
    {
        NormalizationUtility::parseForm('nonsense');
    }

    // ///////////////////////////////////////////////////
    // Tests concerning implementation capabilities
    // ///////////////////////////////////////////////////

    /**
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::isNfdMacCompatible()
     */
    public function testIsNfdMacCompatible()
    {
        $expected = extension_loaded('iconv');
        if ($expected) {
            $level = error_reporting();
            error_reporting(E_ALL);
            set_error_handler(
                function ($errno, $errstr, $errfile, $errline) use (&$expected) {
                    $message = 'iconv(): Wrong charset';
                    if ($message === substr($errstr, 0, strlen($message))) {
                        $expected = false;
                    }
                },
                E_ALL
            );
            iconv('utf-8', 'utf-8-mac', 'xxx');
            restore_error_handler();
            error_reporting($level);
        }
        $this->assertSame($expected, NormalizationUtility::isNfdMacCompatible());
    }

    /**
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::isStrictImplementation();
     */
    public function testIsStrictImplementation()
    {
        $isStrict = NormalizationUtility::isStrictImplementation();
        if (ConfigurationHandler::isPolyfillImplementation()) {
            $this->assertFalse($isStrict);
        } else {
            $this->assertTrue($isStrict);
        }
    }

    // ///////////////////////////////////////////////////
    // Tests concerning unicode capabilities
    // ///////////////////////////////////////////////////

    /**
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::detectUnicodeVersion()
     */
    public function testDetectUnicodeVersion()
    {
        // Use the autoloader here !
        if (is_a('Normalizer', MissingNormalizer::class, true)) {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionCode(1519488534);
            $this->expectExceptionMessage('Could not determine unicode version.');
            NormalizationUtility::detectUnicodeVersion();

            return;
        }
        $unicodeVersion = NormalizationUtility::detectUnicodeVersion();
        $this->assertSame(1, preg_match('/^[1-9][0-9]*\.[0-9]+\.[0-9]+$/', $unicodeVersion));
        $this->assertTrue(version_compare('0.0.0', $unicodeVersion, '<'));
        if (ConfigurationHandler::isPolyfillImplementation()) {
            $this->assertSame('7.0.0', $unicodeVersion);
        }
    }
}
