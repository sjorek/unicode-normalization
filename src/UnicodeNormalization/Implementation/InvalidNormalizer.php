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

namespace Sjorek\UnicodeNormalization\Implementation;

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation;

/**
 * Normalizer implementation, used if the auto-loading was somehow skipped.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
abstract class InvalidNormalizer implements NormalizerInterface
{
    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * @param null|bool|int|string $form [optional] Set normalization form, default: NFC
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function __construct($form = null)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function getForm()
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function setForm($form)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function isNormalized($input, $form = null)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalize($input, $form = null)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalizeStringTo($input, $form = null)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalizeTo($input, $form = null)
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public static function getUnicodeVersion()
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Throw a InvalidNormalizerImplementation as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidNormalizerImplementation
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public static function getNormalizationForms()
    {
        throw self::createInvalidNormalizerImplementationException();
    }

    /**
     * Creates an InvalidNormalizerImplementation exception, stating that the autoloader should not be skipped.
     *
     * @return InvalidNormalizerImplementation
     */
    protected static function createInvalidNormalizerImplementationException()
    {
        return new InvalidNormalizerImplementation(
            'This unicode normalizer implementation is invalid. Do not skip the autoloader!',
            1520071585
        );
    }
}
