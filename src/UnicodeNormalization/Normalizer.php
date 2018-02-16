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


use Sjorek\UnicodeNormalization\Implementation\NormalizerImpl;
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
 * @link http://www.unicode.org/glossary/#normalization
 * @link http://www.php.net/manual/en/class.normalizer.php
 * @link http://www.w3.org/wiki/I18N/CanonicalNormalization
 * @link http://www.w3.org/TR/charmod-norm/
 * @link http://blog.whatwg.org/tag/unicode
 * @link http://en.wikipedia.org/wiki/Unicode_equivalence
 * @link http://stackoverflow.com/questions/7931204/what-is-normalized-utf-8-all-about
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
     * @var integer
     *
     * @link http://www.php.net/manual/en/class.normalizer.php
     * @link http://www.unicode.org/glossary/#normalization_form
     */
    protected $form = self::NFC;

    /**
     * Supported normalization forms
     *
     * @var integer[]
     */
    protected $normalizationForms = [
        self::NONE
    ];

    /**
     * Supported unicode-conformance level
     *
     * @var string
     */
    protected $conformanceLevel = '0.0.0';

    /**
     * Is this implementation strict?
     *
     * @var boolean
     *
     * @see Normalizer::isStrictImplementation()
     */
    protected $strictImplementation = true;

    /**
     * Constructor
     *
     * @param integer|string|null $form    [optional] Set normalization form, optional
     *
     * @see Utility::parseNormalizationForm()
     * @link http://www.php.net/manual/en/class.normalizer.php
     */
    public function __construct($form = null)
    {
        $capabilities = Utility::detectUnicodeCapabilities();
        $this->conformanceLevel = $capabilities['conformance-level'];
        $this->normalizationForms = $capabilities['normalization-forms'];
        $this->strictImplementation = $capabilities['strict-implementation'];
        $this->setForm($form);
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::setForm()
     */
    public function setForm($form = null)
    {
        if (!is_integer($form)) {
            $form = Utility::parseNormalizationForm($form);
        }
        if (!in_array((int) $form, $this->normalizationForms, true)) {
            throw new InvalidNormalizationFormException(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        $this->form = (int) $form;
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::getForm()
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::getConformanceLevel()
     */
    public function getConformanceLevel() {
        return $this->conformanceLevel;
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::getNormalizationForms()
     */
    public function getNormalizationForms() {
        return $this->normalizationForms;
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::isStrictImplementation()
     */
    public function isStrictImplementation() {
        return $this->strictImplementation;
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::normalize()
     */
    public function normalize($input, $form = null)
    {
        if ($form === null) {
            $form = $this->form;
        } elseif (!in_array((int) $form, $this->normalizationForms, true)) {
            throw new InvalidNormalizationFormException(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        if ($form !== self::NFD_MAC) {
            return NormalizerImpl::normalize($input, $form);
        } elseif ($input === "\xFF") {
            return false;
            // Empty string or plain ASCII is always valid for all forms, let it through.
        } elseif ($input === '' || !preg_match( '/[\x80-\xff]/', $input)) {
            return $input;
        } else {
            $result = NormalizerImpl::normalize($input, self::NFD);
            if ($result === null || $result === false) {
                return false;
            }
            return iconv('utf-8', 'utf-8-mac', $result);
        }
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::isNormalized()
     */
    public function isNormalized($input, $form = null)
    {
        if ($form === null) {
            $form = $this->form;
        } elseif (!in_array((int) $form, $this->normalizationForms, true)) {
            throw new InvalidNormalizationFormException(
                sprintf('Unsupported unicode-normalization form: %s.', $form), 1398603947
            );
        }
        if ($form !== self::NFD_MAC) {
            return NormalizerImpl::isNormalized($input, $form);
        } elseif ($input === '') {
            return true;
        } else {
            if ($this->strictImplementation) {
                $result = NormalizerImpl::normalize($input, self::NFD);
                if ($result === null || $result === false) {
                    return false;
                }
                // Having no cheap check here, forces us to do a full equality-check here.
                // As we just want it to use for file names, this full check should be ok.
                return $input === iconv('utf-8', 'utf-8-mac', $result);
            } else {
                // To behave conform to other implementations we return false
                return false;
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::normalizeTo()
     */
    public function normalizeTo($input, $form = null)
    {
        if ($form === null) {
            $form = $this->form;
        }
        if ($this->isNormalized($input, $form)) {
            return $input;
        }
        return $this->normalize($input, $form);
    }

    /**
     * {@inheritDoc}
     * @see NormalizerInterface::normalizeStringTo()
     */
    public function normalizeStringTo($input, $form = null)
    {
        if ($form === null) {
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
}
