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

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;

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
class BaseNormalizer implements NormalizerInterface
{
    /**
     * Array of supported normalization forms.
     *
     * @var array
     */
    const NORMALIZATION_FORMS = [
        self::NONE,
        self::NFD,
        self::NFKD,
        self::NFC,
        self::NFKC,
    ];

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
     * @param null|bool|int|string $form [optional] Set normalization form, default: NFC
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
        $this->form = $this->getFormArgument($form);
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
        $form = $this->getFormArgument($form);

        return \Normalizer::normalize($input, $form);
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::isNormalized()
     */
    public function isNormalized($input, $form = null)
    {
        $form = $this->getFormArgument($form);

        return \Normalizer::isNormalized($input, $form);
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
        $form = $this->getFormArgument($form);
        if (self::NONE < $form) {
            return $this->normalizeTo($input, $form);
        }

        return $input;
    }

    /**
     * @param null|int $form
     *
     * @throws InvalidNormalizationForm
     *
     * @return int
     */
    protected function getFormArgument($form)
    {
        if (null === $form) {
            return $this->form;
        }
        $form = (int) $form;
        if (in_array($form, static::NORMALIZATION_FORMS, true)) {
            return $form;
        }
        throw new InvalidNormalizationForm(
            sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
        );
    }

    protected static $unicodeVersion = null;

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::getUnicodeVersion()
     */
    public static function getUnicodeVersion()
    {
        if (null === self::$unicodeVersion) {
            return self::$unicodeVersion = NormalizationUtility::detectUnicodeVersion();
        }

        return self::$unicodeVersion;
    }

    /**
     * {@inheritdoc}
     *
     * @see NormalizerInterface::getNormalizationForms()
     */
    public static function getNormalizationForms()
    {
        return static::NORMALIZATION_FORMS;
    }
}
