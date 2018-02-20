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

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\NormalizationUtility;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationUtilityTest extends AbstractTestCase
{
    // ///////////////////////////////////////////////////
    // Tests concerning normalization form string parsing
    // ///////////////////////////////////////////////////

    public function provideCheckParseFormData()
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
     * @test
     * @dataProvider provideCheckParseFormData
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::parseForm()
     *
     * @param int   $expected
     * @param mixed $form
     */
    public function checkParseForm($expected, $form)
    {
        $this->assertSame($expected, NormalizationUtility::parseForm($form));
    }

    /**
     * @test
     * @expectedException           \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @expectedExceptionMessage    Invalid unicode normalization form value: nonsense
     * @expectedExceptionCode       1398603947
     * @covers \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::parseForm()
     */
    public function checkParseFormThrowsInvalidNormalizationFormException()
    {
        NormalizationUtility::parseForm('nonsense');
    }

    // ///////////////////////////////////////////////////
    // Tests concerning unicode capabilities
    // ///////////////////////////////////////////////////

    /**
     * @test
     * @runInSeparateProcess
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::detectCapabilities()
     */
    public function checkDetectUnicodeCapabilities()
    {
        $capabilities = NormalizationUtility::detectCapabilities();

        $this->assertTrue(is_array($capabilities));

        $keys = array_keys($capabilities);
        $this->assertTrue(sort($keys));
        $this->assertEquals(['forms', 'level', 'strict'], $keys);

        $this->assertArrayHasKey('forms', $capabilities);
        $this->assertTrue(in_array(NormalizerInterface::NONE, $capabilities['forms'], true));

        $this->assertArrayHasKey('level', $capabilities);
        $this->assertTrue((bool) preg_match('/[0-9]+\.[0-9]+\.[0-9]+/', $capabilities['level']));
        $this->assertTrue(version_compare('0.0.0.0', $capabilities['level'], '<='));

        $this->assertArrayHasKey('strict', $capabilities);
        $this->assertTrue(is_bool($capabilities['strict']));
    }

    // ///////////////////////////////////////////////////
    // Tests concerning unicode normalizer implementation
    // ///////////////////////////////////////////////////

    /**
     * @return string[][]
     */
    public function provideCheckRegisterImplementation()
    {
        return [
            'intl extension' => [
                NormalizationUtility::IMPLEMENTATION_INTL,
            ],
            'patchwork/utf8 package' => [
                NormalizationUtility::IMPLEMENTATION_PATCHWORK,
            ],
            'symfony/polyfill-intl-normalizer package' => [
                NormalizationUtility::IMPLEMENTATION_SYMFONY,
            ],
            'stub implementation' => [
                NormalizationUtility::IMPLEMENTATION_STUB,
            ],
        ];
    }

    /**
     * @test
     * @runInSeparateProcess
     * @dataProvider provideCheckRegisterImplementation
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::getImplementation()
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::registerImplementation()
     */
    public function checkRegisterImplementation($implementationClass)
    {
        $this->assertTrue(NormalizationUtility::registerImplementation($implementationClass));
        $this->assertFalse(NormalizationUtility::registerImplementation($implementationClass));
        $this->assertSame($implementationClass, NormalizationUtility::getImplementation());
        $this->assertTrue(
            is_a(
                \Sjorek\UnicodeNormalization\Implementation\NormalizerImpl::class,
                $implementationClass,
                true
            )
        );
    }

    /**
     * @test
     * @runInSeparateProcess
     * @expectedException           \Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation
     * @expectedExceptionMessage    The given normalizer implementation does not exist: NoneExistentImplementation
     * @expectedExceptionCode       1519042943
     * @covers \Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::registerImplementation()
     */
    public function checkRegisterImplementationThrowsInvalidNormalizerImplementation()
    {
        NormalizationUtility::registerImplementation('NoneExistentImplementation');
    }

    /**
     * @test
     * @runInSeparateProcess
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::getImplementation()
     * @covers \Sjorek\UnicodeNormalization\NormalizationUtility::registerImplementation()
     */
    public function checkGetImplementation()
    {
        $this->assertTrue(
            in_array(
                NormalizationUtility::getImplementation(),
                [
                    NormalizationUtility::IMPLEMENTATION_INTL,
                    NormalizationUtility::IMPLEMENTATION_SYMFONY,
                    NormalizationUtility::IMPLEMENTATION_PATCHWORK,
                    NormalizationUtility::IMPLEMENTATION_STUB,
                ],
                true
            )
        );
    }

    // ///////////////////////////////////////////////////
    // Tests concerning iconv implementation
    // ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function checkAppleIconvIsAvailable()
    {
        $expected = extension_loaded('iconv');
        if ($expected) {
            $level = error_reporting();
            error_reporting(E_ALL);
            set_error_handler(
                function ($errno, $errstr, $errfile, $errline) use (&$expected) {
                    if ('iconv(): Wrong charset' === substr($errstr, 0, strlen('iconv(): Wrong charset'))) {
                        $expected = false;
                    }
                },
                E_ALL
            );
            iconv('utf-8', 'utf-8-mac', 'xxx');
            restore_error_handler();
            error_reporting($level);
        }
        $this->assertSame($expected, NormalizationUtility::appleIconvIsAvailable());
    }
}
