<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Validator;


/**
 * Class for validating and sanitizing the normalization of unicode-based URIs.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UriValidator
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
        if ($stringValidator === null)
        {
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
     * @param string  $uri      The uri to filter
     * @param integer $form     [optional] normalization form to check against, overriding the default
     * @param string  $charset  [optional] charset to try to convert from, default is ISO-8859-1
     * @return string
     * @see \Patchwork\Utf8\Bootup::filterRequestUri()
     * @todo Feature #57695: Keep this method in sync patchwork's implementation
     * @todo Feature #57695: Figure out why patchwork's implementation assumes Windows-CP1252 as fallback
     */
    public function sanitize($uri, $form = null, $charset = null)
    {

        // is url empty or is the url-decoded url already valid utf8 ?
        if ($uri === '' || !preg_match('/[\x80-\xFF]/', urldecode($uri))) {
            return $uri;
        }

        // encode all unencoded single-byte characters from 128 to 255
        $uri = preg_replace_callback(
            '/[\x80-\xFF]+/',
            function ($match) {
                return urlencode($match[0]);
            },
            $uri
        );

        $stringValidator = $this->stringValidator;
        // encode all unencoded multibyte-byte characters from 128 and above
        // for combined characters in NFD we need to prepend the preceding character
        $uri = preg_replace_callback(
            '/(^|.)(?:%[89A-F][0-9A-F])+/i',
            // url-decode -> utf8-encode -> url-encode
            function ($match) use($stringValidator, $form, $charset) {
                return urlencode($stringValidator->sanitize(urldecode($match[0]), $form, $charset));
            },
            $uri
        );

        return $uri;
    }

    /**
     * Test if given uri is properly url-encoded with well-formed and normalized UTF-8.
     *
     * @param string  $uri      The uri to to test
     * @param integer $form     [optional] normalization form to check against, overriding the default
     * @param string  $charset  [optional] charset to try to convert from, default is ISO-8859-1
     * @return boolean TRUE if the string is a well-formed and normalized UTF-8 string.
     */
    public function isValid($uri, $form = null, $charset = null) {
        return $uri === '' || $uri === $this->sanitize($uri, $form, $charset);
    }
}
