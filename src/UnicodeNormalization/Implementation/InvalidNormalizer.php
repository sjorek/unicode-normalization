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

use Sjorek\UnicodeNormalization\Exception\InvalidRuntimeFailure;

/**
 * Normalizer implementation, used if the auto-loading was somehow skipped.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
final class InvalidNormalizer implements NormalizerInterface
{
    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * @param null|bool|int|string $form [optional] Set normalization form, default: NFC
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function __construct($form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function getForm()
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function setForm($form)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function isNormalized($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalize($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalizeStringTo($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public function normalizeTo($input, $form = null)
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public static function getUnicodeVersion()
    {
        throw self::createException();
    }

    /**
     * Throw a InvalidRuntimeFailure as the autoloader was skipped somehow.
     *
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     *
     * @throws InvalidRuntimeFailure
     *
     * @see \Sjorek\UnicodeNormalization\Utility\AutoloadUtility::register()
     */
    public static function getNormalizationForms()
    {
        throw self::createException();
    }

    /**
     * Creates an InvalidRuntimeFailure exception, stating that the autoloader should not be skipped.
     *
     * @return InvalidRuntimeFailure
     */
    private static function createException()
    {
        return new InvalidRuntimeFailure(
            'This unicode normalizer implementation is invalid. Do not skip the autoloader!',
            1520071585
        );
    }
}
