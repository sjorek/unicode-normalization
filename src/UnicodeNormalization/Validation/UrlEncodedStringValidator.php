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

namespace Sjorek\UnicodeNormalization\Validation;

/**
 * Class for validating and sanitizing the normalization of unicode-based url-encoded strings.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UrlEncodedStringValidator
{
    /**
     * @var StringValidator
     */
    protected $stringValidator;

    /**
     * @param StringValidator $stringValidator
     */
    public function __construct(StringValidator $stringValidator = null)
    {
        if (null === $stringValidator) {
            $stringValidator = new StringValidator();
        }
        $this->stringValidator = $stringValidator;
    }

    /**
     * Ensures the URI is well formed UTF-8.
     * When not, assumes ISO-8859-1 and re-encodes the URL to the corresponding UTF-8 encoded equivalent.
     *
     * The implementation is adopted from \Patchwork\Utf8\Bootup::filterRequestUri() and tweaked for our needs.
     *
     * @param string $uri     The uri to filter
     * @param int    $form    [optional] normalization form to check against, overriding the default
     * @param string $charset [optional] charset to convert from, default is `mb_detect_encoding()` or ISO-8859-1
     *
     * @return false|string
     *
     * @see \Patchwork\Utf8\Bootup::filterRequestUri()
     *
     * @todo Keep this method in sync patchwork's implementation
     * @todo Figure out why patchwork's implementation used Windows-CP1252 as fallback
     */
    public function filter($input, $form = null, $charset = null)
    {
        // is url empty or is the url-decoded url already valid utf8 ?
        if ('' === $input || !preg_match('/[\x80-\xFF]/', urldecode($input))) {
            return $input;
        }

        // encode all unencoded single-byte characters from 128 to 255
        $input = preg_replace_callback(
            '/[\x80-\xFF]+/',
            function ($match) {
                return urlencode($match[0]);
            },
            $input
        );
        if (null === $input) {
            return false;
        }

        $validator = $this->stringValidator;
        // encode all multibyte-byte characters from 128 and above
        $input = preg_replace_callback(
            '/(^|.)(?:%[89A-F][0-9A-F])+/i',
            // url-decode -> utf8-encode -> url-encode
            function ($match) use ($validator, $form, $charset) {
                return urlencode($validator->filter(urldecode($match[0]), $form, $charset));
            },
            $input
        );

        return $input === null ? false : $input;
    }

    /**
     * Test if given uri is properly url-encoded with well-formed and normalized UTF-8.
     *
     * @param string $uri     The uri to to test
     * @param int    $form    [optional] normalization form to check against, overriding the default
     * @param string $charset [optional] charset to convert from, default is `mb_detect_encoding()` or ISO-8859-1
     *
     * @return bool TRUE if the string is a well-formed and normalized UTF-8 string
     */
    public function isValid($uri, $form = null, $charset = null)
    {
        return '' === $uri || $uri === $this->filter($uri, $form, $charset);
    }
}
