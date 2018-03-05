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

use PHPUnit\Framework\TestCase;

/**
 * Base test case class for all unit-tests.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AbstractTestCase extends TestCase
{
    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @param mixed  $objectOrClass
     * @param string $methodName
     * @param array  $arguments
     *
     * @return mixed
     */
    protected function callProtectedMethod($objectOrClass, $methodName, ...$arguments)
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        $result = $method->invokeArgs(is_object($objectOrClass) ? $objectOrClass : null, $arguments);
        $method->setAccessible(false);

        return $result;
    }

    /**
     * @param mixed  $object
     * @param string $propertyName
     * @param mixed  $objectOrClass
     *
     * @return mixed
     */
    protected function getProtectedProperty($objectOrClass, $propertyName)
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue(is_object($objectOrClass) ? $objectOrClass : null);
        $property->setAccessible(false);

        return $result;
    }

    /**
     * @param mixed  $object
     * @param string $propertyName
     * @param mixed  $value
     * @param mixed  $objectOrClass
     *
     * @return mixed
     */
    protected function setProtectedProperty($objectOrClass, $propertyName, $value)
    {
        $class = new \ReflectionClass(is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue(is_object($objectOrClass) ? $objectOrClass : null, $value);
        $property->setAccessible(false);
    }

    /**
     * @param string $options
     * @param int    $limit
     *
     * @see \debug_backtrace()
     */
    protected function trace($options = DEBUG_BACKTRACE_IGNORE_ARGS, $limit = 0)
    {
        $trace = debug_backtrace($options, $limit);
        array_shift($trace);
        $this->debug($trace);
    }

    /**
     * @param mixed $payload
     */
    protected function debug($payload)
    {
        $result = file_put_contents(
            'phpunit-debug.log',
            json_encode([str_pad('', 79, '-'), $payload], JSON_PRETTY_PRINT) . PHP_EOL,
            FILE_APPEND
        );
        if (false === $result) {
            throw new \Exception('Failed to write to log-file: phpunit-debug.log');
        }
    }
}
