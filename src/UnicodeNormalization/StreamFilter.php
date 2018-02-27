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

namespace Sjorek\UnicodeNormalization;

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;

/**
 * A stream-filter implementation for normalizing unicode, currently only utf8.
 *
 * @see Normalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StreamFilter extends \php_user_filter
{
    /**
     * @var string
     */
    const DEFAULT_NAMESPACE = 'convert.unicode-normalization';

    /**
     * 0x80 for 0b0xxxxxxx.
     *
     * @var int
     */
    const BYTE_SINGLE = 0b10000000;

    /**
     * 0xC0 for 0b10xxxxxx.
     *
     * @var int
     */
    const BYTE_PAYLOAD = 0b11000000;

    /**
     * 0xE0 for 0b110xxxxx.
     *
     * @var int
     */
    const BYTE_DOUBLE = 0b11100000;

    /**
     * 0xF0 for 0b1110xxxx.
     *
     * @var int
     */
    const BYTE_TRIPLE = 0b11110000;

    /**
     * 0xF8 for 0b11110xxx.
     *
     * @var int
     */
    const BYTE_QUAD = 0b11111000;

    // DISABLED, AS IT IS NOT STANDARD …
    // /**
    //  * 0xFC for 0b111110xx
    //  *
    //  * @var int
    //  */
    // const BYTE_PENTA = 0b11111100;

    // DISABLED, AS IT IS NOT STANDARD …
    // /**
    //  * 0xFE for 0b1111110x
    //  *
    //  * @var int
    //  */
    // const BYTE_HEXA = 0b11111110;

    /**
     * 0xFF for 0b11111111.
     *
     * @var int
     */
    const BYTE_MASK = 0b11111111;

    /**
     * @var null|string
     */
    protected static $namespace = null;

    /**
     * @var null|NormalizerInterface
     */
    protected static $normalizer = null;

    /**
     * @var int
     */
    protected $form;

    /**
     * @param string              $namespace
     * @param NormalizerInterface $normalizer
     *
     * @return bool
     */
    public static function register($namespace = self::DEFAULT_NAMESPACE, NormalizerInterface $normalizer = null)
    {
        // already registered or invalid namespace ?
        // TODO throw an exception for invalid namespaces?
        if (null !== static::$namespace || '.*' === substr($namespace, -2)) {
            return false;
        }
        if (stream_filter_register($namespace, static::class) &&
            stream_filter_register(sprintf('%s.*', $namespace), static::class)) {
            static::$namespace = $namespace;
            static::$normalizer = $normalizer ?: new Normalizer();

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @see \php_user_filter::onCreate()
     *
     * @throws \Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm
     */
    public function onCreate()
    {
        if (static::$namespace === $this->filtername) {
            if (isset($this->params)) {
                $this->form = NormalizationUtility::parseForm($this->params);
            } else {
                $this->form = self::$normalizer->getForm();
            }

            return true;
        }
        if (false !== strpos($this->filtername, '.')) {
            list($form, $namespace) = explode('.', strrev($this->filtername), 2);
            $namespace = strrev($namespace);
            $form = strrev($form);
            if (static::$namespace === $namespace) {
                $this->form = NormalizationUtility::parseForm($form);

                return true;
            }
        }

        // Some other normalize filter was asked for,
        // report failure so that PHP will keep looking
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @see \php_user_filter::filter()
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        $normalizer = static::$normalizer;
        while ($bucket = stream_bucket_make_writeable($in)) {
            $result = $this->processStringFragment($bucket->data, $bucket->datalen, $normalizer);
            if (false === $result) {
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
     * @param string              $fragment
     * @param int                 $fragmentSize
     * @param NormalizerInterface $normalizer
     *
     * @return array|bool
     */
    protected function processStringFragment($fragment, $fragmentSize, NormalizerInterface $normalizer)
    {
        if ('' === $fragment || 0 === $fragmentSize || 1 > static::getCodePointSize($fragment[0])) {
            return false;
        }

        $payloadSize = 1;
        foreach ([1, 2, 3, 4] as $offset) {
            if (abs($offset) > $fragmentSize) {
                return false;
            }

            $codePointSize = static::getCodePointSize($fragment[$fragmentSize - $offset]);
            if ($codePointSize < 0) {
                return false;
            }
            if (0 === $codePointSize) {
                ++$payloadSize;
                continue;
            }
            if ($codePointSize === $offset && $codePointSize === $payloadSize) {
                // nothing to do here!
            } else {
                $fragmentSize -= $offset;
                $fragment = substr($fragment, 0, $fragmentSize);
            }

            $result = $normalizer->normalizeStringTo($fragment, $this->form);
            if (false === $result || null === $result) {
                return false;
            }

            return [$result, $fragmentSize];
        }

        return false;
    }

    /**
     * @param mixed $byte
     *
     * @return int
     */
    protected static function getCodePointSize($byte)
    {
        $byte = ord($byte) & self::BYTE_MASK;
        if ($byte < self::BYTE_SINGLE) {
            return 1;
        }
        if ($byte < self::BYTE_PAYLOAD) {
            return 0;
        }
        if ($byte < self::BYTE_DOUBLE) {
            return 2;
        }
        if ($byte < self::BYTE_TRIPLE) {
            return 3;
        }
        if ($byte < self::BYTE_QUAD) {
            return 4;
            // DISABLED, AS IT IS NOT STANDARD …
        // } elseif ($byte < self::BYTE_PENTA) {
        //     return 5;
        // } elseif ($byte < self::BYTE_HEXA) {
        //     return 6;
        }

        return -1;
    }
}
