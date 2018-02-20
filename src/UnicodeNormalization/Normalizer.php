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

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;

/**
 * Class for normalizing unicode.
 *
 *    “Normalization: A process of removing alternate representations of equivalent
 *    sequences from textual data, to convert the data into a form that can be
 *    binary-compared for equivalence. In the Unicode Standard, normalization refers
 *    specifically to processing to ensure that canonical-equivalent (and/or
 *    compatibility-equivalent) strings have unique representations.”
 *
 *     -- quoted from unicode glossary linked below
 *
 * @see http://www.unicode.org/glossary/#normalization
 * @see http://www.php.net/manual/en/class.normalizer.php
 * @see http://www.w3.org/wiki/I18N/CanonicalNormalization
 * @see http://www.w3.org/TR/charmod-norm/
 * @see http://blog.whatwg.org/tag/unicode
 * @see http://en.wikipedia.org/wiki/Unicode_equivalence
 * @see http://stackoverflow.com/questions/7931204/what-is-normalized-utf-8-all-about
 * @see http://php.net/manual/en/class.normalizer.php
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class Normalizer implements NormalizerInterface
{
    /**
     * NONE or one of the five unicode normalization forms NFC, NFD, NFKC, NFKD or NFD_MAC.
     *
     * Must be set to one of the integer constants from above. Defaults to NFC.
     *
     * @var int
     *
     * @see http://www.php.net/manual/en/class.normalizer.php
     * @see http://www.unicode.org/glossary/#normalization_form
     */
    protected $form = self::NFC;

    /**
     * Constructor.
     *
     * @param null|int|string $form [optional] Set normalization form, optional
     *
     * @see NormalizationUtility::parseForm()
     */
    public function __construct($form = null)
    {
        if (null !== $form) {
            $this->setForm($form);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::setForm()
     */
    public function setForm($form)
    {
        if (!is_int($form)) {
            $form = NormalizationUtility::parseForm($form);
        }
        if (!in_array((int) $form, self::getCapabilities()['forms'], true)) {
            throw new InvalidNormalizationForm(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        $this->form = (int) $form;
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::getForm()
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::normalize()
     */
    public function normalize($input, $form = null)
    {
        if (null === $form) {
            $form = $this->form;
        } elseif (!in_array((int) $form, self::getCapabilities()['forms'], true)) {
            throw new InvalidNormalizationForm(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        if (self::NFD_MAC !== $form) {
            return Implementation\NormalizerImpl::normalize($input, $form);
        }
        if ("\xFF" === $input) {
            return false;
        }
        // Empty string or plain ASCII is always valid for all forms, let it through.
        if ('' === $input || !preg_match('/[\x80-\xFF]/', $input)) {
            return $input;
        }
        $result = Implementation\NormalizerImpl::normalize($input, self::NFD);
        if (null === $result || false === $result) {
            return false;
        }

        return iconv('utf-8', 'utf-8-mac', $result);
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::isNormalized()
     */
    public function isNormalized($input, $form = null)
    {
        if (null === $form) {
            $form = $this->form;
        } elseif (!in_array((int) $form, self::getCapabilities()['forms'], true)) {
            throw new InvalidNormalizationForm(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        if (self::NFD_MAC !== $form) {
            return Implementation\NormalizerImpl::isNormalized($input, $form);
        }
        if ('' === $input) {
            return true;
        }
        if (self::getCapabilities()['strict']) {
            $result = Implementation\NormalizerImpl::normalize($input, self::NFD);
            if (null === $result || false === $result) {
                return false;
            }
            // Having no cheap check here, forces us to do a full equality-check here.
            // As we just want it to use for file names, this full check should be ok.
            return $input === iconv('utf-8', 'utf-8-mac', $result);
        }
        // To behave conform to other implementations we return false
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::normalizeTo()
     */
    public function normalizeTo($input, $form = null)
    {
        if ($this->isNormalized($input, $form)) {
            return $input;
        }

        return $this->normalize($input, $form);
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::normalizeStringTo()
     */
    public function normalizeStringTo($input, $form = null)
    {
        if (null === $form) {
            $form = $this->form;
        }
        if (self::NONE < (int) $form) {
            if ($this->isNormalized($input, $form)) {
                return $input;
            }

            return $this->normalize($input, $form);
        }

        return $input;
    }

    /**
     * The unicode normalization capabilities of the underlying implementation.
     *
     * @var array
     *
     * @see Normalizer::getCapabilities()
     */
    protected static $capabilities = null;

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::getCapabilities()
     */
    public static function getCapabilities()
    {
        if (null === static::$capabilities) {
            static::$capabilities = NormalizationUtility::detectCapabilities();
        }

        return static::$capabilities;
    }
}
