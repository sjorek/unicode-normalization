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

/**
 * Interface for the unicode normalizer facade.
 * Additionally required form constants are defined here.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
interface NormalizerInterface extends NormalizationForms
{
    /**
     * Set the default normalization form to the given value.
     *
     * @param null|bool|int|string| $form
     *
     * @see \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm()
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidFormFailure
     */
    public function setForm($form);

    /**
     * Retrieve the current normalization-form constant.
     *
     * @return int
     */
    public function getForm();

    /**
     * Normalizes the input provided and returns the normalized string.
     *
     * @param string $input the input string to normalize
     * @param int    $form  [optional] One of the normalization forms
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidFormFailure
     *
     * @return string the normalized string or NULL if an error occurred
     *
     * @see http://php.net/manual/en/normalizer.normalize.php
     */
    public function normalize($input, $form = null);

    /**
     * Checks if the provided string is in the specified normalization form.
     *
     * @param string $input The input string to check
     * @param int    $form  [optional] One of the normalization forms
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidFormFailure
     *
     * @return bool TRUE if normalized, FALSE otherwise or if an error occurred
     *
     * @see http://php.net/manual/en/normalizer.isnormalized.php
     */
    public function isNormalized($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $form and returns the normalized string.
     *
     * Calls underlying implementation even if given $form is NONE, but it normalizes only if needed.
     *
     * @param string $input the string to normalize
     * @param int    $form  [optional] normalization form to use, overriding the default
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidFormFailure
     *
     * @return null|string Normalized string or null if an error occurred
     *
     * @see NormalizerInterface::normalize()
     * @see NormalizerInterface::isNormalized()
     */
    public function normalizeTo($input, $form = null);

    /**
     * Normalizes the $string provided to the given or default $form and returns the normalized string.
     *
     * Does not call underlying implementation if given normalization is NONE and normalizes only if needed.
     *
     * @param string $input the string to normalize
     * @param int    $form  [optional] normalization form to use, overriding the default
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidFormFailure
     *
     * @return null|string Normalized string or null if an error occurred
     *
     * @see NormalizerInterface::normalize()
     * @see NormalizerInterface::isNormalized()
     */
    public function normalizeStringTo($input, $form = null);

    /**
     * Get the supported unicode version level as version triple ("X.Y.Z").
     *
     * @return string
     */
    public static function getUnicodeVersion();

    /**
     * Get the supported unicode normalization forms as array.
     *
     * @return int[]
     */
    public static function getNormalizationForms();
}
