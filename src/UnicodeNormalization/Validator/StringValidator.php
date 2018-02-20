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

namespace Sjorek\UnicodeNormalization\Validator;

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\Normalizer;

/**
 * Class for validating and sanitizing the normalization of unicode-strings.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidator
{
    /**
     * @var Normalizer
     */
    protected $normalizer;

    /**
     * @param Normalizer $normalizer
     */
    public function __construct(NormalizerInterface $normalizer = null)
    {
        if (null === $normalizer) {
            $normalizer = new Normalizer();
        }
        $this->normalizer = $normalizer;
    }

    /**
     * The string '\xe2\x97\x8c' is equivalent to '◌' a combining character as defined in the glossary
     * linked below. It is meant for internal usage as part of NFC-compatible string-filter methods below.
     *
     * @var string
     *
     * @see http://www.unicode.org/glossary/#combining_character
     */
    const LEADING_COMBINATOR = "\xe2\x97\x8c";

    /**
     * Ensures that given input is a well-formed and normalized UTF-8 string.
     *
     * This implementation has been shamelessly taken from the “patchwork/utf8”
     * package's “Bootup::filterString()”-method and tweaked for our needs.
     *
     * @param string $input   The string to filter
     * @param int    $form    [optional] normalization form to apply, overriding the default
     * @param string $charset [optional] charset to try to convert from, default is ISO-8859-1
     *
     * @return string|false The converted string or false if given charset is unknown
     *
     * @see \Patchwork\Utf8\Bootup::filterString()
     */
    public function sanitize($input, $form = null, $charset = null)
    {
        // TODO Move workaround for https://bugs.php.net/65732, fixed for PHP ≥7.0.11, into separate class.
        if (version_compare(PHP_VERSION, '7.0.11', '<') && false !== strpos($input, "\r")) {
            $self = $this;
            $input = explode("\r", $input);
            $input = array_map(
                function ($string) use ($self, $form, $charset) {
                    $self->sanitize($string, $form, $charset);
                },
                $input
            );

            return in_array(false, $input, true) ? false : implode("\r", $input);
        }
        if (preg_match('/[\x80-\xFF]/', $input) || !preg_match('//u', $input)) {
            $normalized = $this->normalizer->normalizeStringTo($input, $form);
            if (isset($normalized[0]) && preg_match('//u', $normalized)) {
                $input = $normalized;
            } elseif (false === ($input = $this->convertStringToUtf8($input, $charset))) {
                return false;
            }
            if ($input[0] >= "\x80" && isset($normalized[0]) && preg_match('/^\p{Mn}/u', $input)) {
                // Prepend leading combining chars for NFC-safe concatenations.
                $input = self::LEADING_COMBINATOR . $input;
            }
        }

        return $input;
    }

    /**
     * Test if given input is a well-formed and normalized UTF-8 string.
     *
     * @param string $input   The string to check
     * @param int    $form    [optional] normalization form to check against, overriding the default
     * @param string $charset [optional] charset to try to convert from, default is ISO-8859-1
     *
     * @return bool TRUE if the string is a well-formed and normalized UTF-8 string
     */
    public function isValid($input, $form = null, $charset = null)
    {
        return $input === $this->sanitize($input, $form);
    }

    /**
     * @param string $input   The string to filter
     * @param string $charset [optional] charset to try to convert from, default is ISO-8859-1
     *
     * @return string|false The converted string or false if given charset is unknown
     */
    protected function convertStringToUtf8($input, $charset = null)
    {
        return mb_convert_encoding($input, $charset ?: 'iso-8859-1', 'utf-8');
    }
}
