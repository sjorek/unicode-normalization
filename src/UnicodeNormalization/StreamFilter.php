<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization;

/**
 * A stream-filter implementation for normalizing unicode, currently only utf8.
 *
 *    “Normalization: A process of removing alternate representations of equivalent
 *    sequences from textual data, to convert the data into a form that can be
 *    binary-compared for equivalence. In the Unicode Standard, normalization refers
 *    specifically to processing to ensure that canonical-equivalent (and/or
 *    compatibility-equivalent) strings have unique representations.”
 *
 *     -- quoted from unicode glossary linked below
 *
 * Supported case-insensitive normalization form aliases:
 * <pre>
 * - Ignore/skip unicode-normalization : 1,  NONE, binary, default, validate
 * - Normalization form D              : 2,  NFD, FORM_D, D, form-d, decompose, collation
 * - Normalization form D (mac)        : 32, NFD_MAC, FORM_D_MAC, D_MAC, form-d-mac, nfd-mac, d-mac, mac
 * - Normalization form KD             : 3,  NFKD, FORM_KD, KD, form-kd
 * - Normalization form C              : 4,  NFC, FORM_C, C, form-c, compose, recompose, legacy, html5
 * - Normalization form KC             : 5,  NFKC, FORM_KC, KC, form-kc, matching
 * </pre>
 *
 * Hints:
 * <pre>
 * - The W3C recommends NFC for HTML5 Output.
 * - Mac OS X's HFS+ filesystem uses a NFD variant to store paths. We provide one implementation for this
 *   special variant, but plain NFD works in most cases too. Even if you use something else than NFD or its
 *   variant HFS+ will always use decomposed NFD path-strings if needed. A detailed description is below.
 * </pre>
 *
 * Apple™ Canonical decomposition for HFS Plus filesystems
 *
 *    “For example, HFS Plus (OS X Extended) uses a variant of Normal Form D in
 *    which U+2000 through U+2FFF, U+F900 through U+FAFF, and U+2F800 through U+2FAFF
 *    are not decomposed …”
 *
 *    -- quoted from Apple™'s Technical Q&A 1173 linked below
 *
 *    “The characters with codes in the range u+2000 through u+2FFF are punctuation,
 *    symbols, dingbats, arrows, box drawing, etc. The u+24xx block, for example, has
 *    single characters for things like u+249c "⒜". The characters in this range are
 *    not fully decomposed; they are left unchanged in HFS Plus strings. This allows
 *    strings in Mac OS encodings to be converted to Unicode and back without loss of
 *    information. This is not unnatural since a user would not necessarily expect a
 *    dingbat "⒜" to be equivalent to the three character sequence "(a)" in a file name.
 *
 *    The characters in the range u+F900 through u+FAFF are CJK compatibility ideographs,
 *    and are not decomposed in HFS Plus strings.
 *
 *    So, for the example given earlier, u+00E9 ("é") must be stored as the two Unicode
 *    characters u+0065 and u+0301 (in that order). The Unicode character u+00E9 ("é")
 *    may not appear in a Unicode string used as part of an HFS Plus B-tree key.”
 *
 *    -- quoted from Apple™'s Technical Q&A 1150 linked below
 *
 * @link http://www.unicode.org/glossary/#normalization
 * @link http://www.w3.org/wiki/I18N/CanonicalNormalization
 * @link http://www.w3.org/TR/charmod-norm/
 * @link http://blog.whatwg.org/tag/unicode
 * @link http://en.wikipedia.org/wiki/Unicode_equivalence
 * @link http://stackoverflow.com/questions/7931204/what-is-normalized-utf-8-all-about
 * @link http://www.php.net/manual/en/class.normalizer.php
 * @link https://packagist.org/packages/symfony/polyfill-intl-normalizer
 * @link https://packagist.org/packages/patchwork/utf8
 * @link https://developer.apple.com/library/content/qa/qa1173/_index.html
 * @link https://developer.apple.com/library/content/qa/qa1235/_index.html
 * @link http://dubeiko.com/development/FileSystems/HFSPLUS/tn1150.html#CanonicalDecomposition
 * @link http://php.net/manual/en/function.iconv.php
 * @link https://opensource.apple.com/source/libiconv/libiconv-50/libiconv/lib/utf8mac.h.auto.html
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilter extends \php_user_filter
{
    /**
     * @var int
     */
    public $form;

    const SINGLE_BYTE = 0b10000000; // = 0x80 for 0b0xxxxxxx
    const PAYLOAD_BYTE = 0b11000000; // = 0xC0 for 0b10xxxxxx
    const DOUBLE_BYTE = 0b11100000; // = 0xE0 for 0b110xxxxx
    const TRIPLE_BYTE = 0b11110000; // = 0xF0 for 0b1110xxxx
    const QUAD_BYTE = 0b11111000; // = 0xF8 for 0b11110xxx
//     const PENTA_BYTE = 0b11111100; // = 0xF8 for 0b111110xx
//     const HEXA_BYTE = 0b11111110; // = 0xF8 for 0b1111110x

    const MASK_BYTE = 0b11111111;

    /**
     * @var string|null
     */
    protected static $namespace = null;

    /**
     * @param  string $namespace
     * @return bool
     */
    public static function register($namespace = 'convert.unicode-normalization')
    {
        // already registered or missing dependency ?
        if (static::$namespace !== null || !static::normalizerIsAvailable()) {
            return false;
        }
        $result = stream_filter_register($namespace, static::class);
        if ($result === true) {
            $result = stream_filter_register(sprintf('%s.*', $namespace), __CLASS__);
        }
        if ($result === true) {
            static::$namespace = $namespace;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \php_user_filter::filter()
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $result = $this->processStringFragment($bucket->data, $bucket->datalen);
            if ($result === false) {
                return PSFS_ERR_FATAL;
            }
            list($data, $datalen) = $result;
            $bucket->data = $data;
            $consumed += $datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }

    /**
     * {@inheritDoc}
     * @see \php_user_filter::onCreate()
     */
    public function onCreate()
    {
        if ($this->filtername === static::$namespace) {
            $this->form = $this->parseNormalizationForm($this->params);

            return true;
        } elseif (strpos($this->filtername, '.') !== false) {
            list($form, $namespace) = explode('.', strrev($this->filtername), 2);
            $namespace = strrev($namespace);
            $form = strrev($form);
            if ($namespace === static::$namespace) {
                $this->form = $this->parseNormalizationForm($form);

                return true;
            }
        }
        /* Some other normalize.* filter was asked for,
        report failure so that PHP will keep looking */
        return false;
    }

    /**
     * @param  string     $fragment
     * @param  int        $fragmentSize
     * @return boolean|[]
     */
    protected function processStringFragment($fragment, $fragmentSize)
    {
        if ($fragment === '' || $fragmentSize === 0 || 1 > self::getCodePointSize($fragment[0])) {
            return false;
        }
        $payloadSize = 1;
        foreach (range(1, 4) as $offset) {
            if (abs($offset) > $fragmentSize) {
                return false;
            }

            $codePointSize = self::getCodePointSize($fragment[$fragmentSize - $offset]);
            if ($codePointSize < 0) {
                return false;
            } elseif ($codePointSize === 0) {
                ++$payloadSize;
                continue;
            } elseif ($codePointSize === $offset && $codePointSize === $payloadSize) {
                // nothing to do here!
            } else {
                $fragmentSize -= $offset;
                $fragment = substr($fragment, 0, $fragmentSize);
            }
            $result = $this->normalize($fragment);

            if ($result === false) {
                return false;
            } else {
                return array($result, $fragmentSize);
            }
        }

        return false;
    }

    /**
     * @param  string            $input
     * @return string|false|null
     */
    protected function normalize($input)
    {
        $result = null;
        if ($this->form === Normalizer::NONE) {
            return $input;
        } elseif ($this->form === self::NFD_MAC) {
            $result = Normalizer::normalize($input, Normalizer::NFD);
            if ($result !== null && $result !== false) {
                $result = iconv('utf-8', 'utf-8-mac', $result);
            }
        } else {
            $result = Normalizer::normalize($input, $this->form);
        }

        return ($result === null) ? false : $result;
    }

    /**
     * @param  mixed $byte
     * @return int
     */
    protected static function getCodePointSize($byte)
    {
        $byte = ord($byte) & self::MASK_BYTE;
        if ($byte < self::SINGLE_BYTE) {
            return 1;
        } elseif ($byte < self::PAYLOAD_BYTE) {
            return 0;
        } elseif ($byte < self::DOUBLE_BYTE) {
            return 2;
        } elseif ($byte < self::TRIPLE_BYTE) {
            return 3;
        } elseif ($byte < self::QUAD_BYTE) {
            return 4;
        }

        return -1;
    }

    const NFD_MAC = 32; // 0x2 & 0xF

    protected static $forms = null;

    /**
     * @param  mixed                     $form
     * @throws \InvalidArgumentException
     * @return int
     */
    protected function parseNormalizationForm($form)
    {
        if (static::$forms === null) {
            static::$forms = array();
            if (static::normalizerIsAvailable()) {
                static::$forms = array(
                    // NONE
                    'none' => Normalizer::NONE,
                    'binary' => Normalizer::NONE,
                    'default' => Normalizer::NONE,
                    'validate' => Normalizer::NONE,
                    // NFD
                    'd' => Normalizer::NFD,
                    'nfd' => Normalizer::NFD,
                    'form-d' => Normalizer::NFD,
                    'decompose' => Normalizer::NFD,
                    'collation' => Normalizer::NFD,
                    // NFKD
                    'kd' => Normalizer::NFKD,
                    'nfkd' => Normalizer::NFKD,
                    'form-kd' => Normalizer::NFKD,
                    // NFC
                    'c' => Normalizer::NFC,
                    'nfc' => Normalizer::NFC,
                    'form-c' => Normalizer::NFC,
                    'form_c' => Normalizer::NFC,
                    'html5' => Normalizer::NFC,
                    'legacy' => Normalizer::NFC,
                    'compose' => Normalizer::NFC,
                    'recompose' => Normalizer::NFC,
                    // NFKC
                    'kc' => Normalizer::NFKC,
                    'nfkc' => Normalizer::NFKC,
                    'form-kc' => Normalizer::NFKC,
                    'matching' => Normalizer::NFKC,
                );
                if (static::macIconvIsAvailable()) {
                    static::$forms = array_merge(
                        static::$forms,
                        array(
                            'mac' => static::NFD_MAC,
                            'd-mac' => static::NFD_MAC,
                            'nfd-mac' => static::NFD_MAC,
                            'form-d-mac' => static::NFD_MAC,
                        )
                    );
                }
            }
        }
        if (empty($form)) {
            // Nothing to do here. Throws exception below!
        } elseif (($name = strtolower(strtr((string) $form, '_', '-'))) && isset(static::$forms[$name])) {
            return static::$forms[$name];
        } elseif (in_array((int) $form, static::$forms, true)) {
            return (int) $form;
        }
        throw new \InvalidArgumentException('Invalid normalization form/mode given.', 1507772911);
    }

    /**
     * Return true if all dependencies of the normalizer implementation are met
     *
     * @return bool
     */
    protected static function normalizerIsAvailable()
    {
        return class_exists('Sjorek\\UnicodeNormalization\\Normalizer', true);
    }

    /**
     * Return true if all dependencies of the iconv implementation are met
     *
     * @return bool
     */
    protected static function macIconvIsAvailable()
    {
        $nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        return (
            extension_loaded('iconv') &&
            $mac === @iconv('utf-8', 'utf-8-mac', $nfc) &&
            $nfc === @iconv('utf-8-mac', 'utf-8', $mac)
        );
    }
}
