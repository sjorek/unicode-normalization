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
 * Interface used to supply normalization form constants.
 *
 * DO NOT DEPEND ON THIS INTERFACE!
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
interface NormalizationForms
{
    /**
     * Ignore any decomposition/composition.
     *
     * Ignoring Implementation decomposition/composition, means nothing is automatically normalized.
     * Many Linux- and BSD-filesystems do not normalize paths and filenames, but treat them as binary data.
     * Apple™'s APFS filesystem treats paths and filenames as binary data.
     *
     * @var int
     */
    const NONE = \Normalizer::NONE;

    /**
     * Canonical decomposition.
     *
     *    “A normalization form that erases any canonical differences, and produces a
     *    decomposed result. For example, ä is converted to a + umlaut in this form.
     *    This form is most often used in internal processing, such as in collation.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     *
     * @see http://www.unicode.org/glossary/#normalization_form_d
     * @see https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @see https://developer.apple.com/library/content/qa/qa1235/_index.html
     */
    const NFD = \Normalizer::NFD;

    /**
     * @var int
     *
     * @see NormalizationForms::NFD
     */
    const FORM_D = self::NFD;

    /**
     * Compatibility decomposition.
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and produces a decomposed result: for example, the single ǆ character is
     *    converted to d + z + caron in this form.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     *
     * @see http://www.unicode.org/glossary/#normalization_form_kd
     */
    const NFKD = \Normalizer::NFKD;

    /**
     * @var int
     *
     * @see NormalizationForms::NFKD
     */
    const FORM_KD = self::NFKD;

    /**
     * Canonical decomposition followed by canonical composition.
     *
     *    “A normalization form that erases any canonical differences, and generally produces
     *    a composed result. For example, a + umlaut is converted to ä in this form. This form
     *    most closely matches legacy usage.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * W3C recommends NFC for HTML5 output and requires NFC for HTML5-compliant parser implementations.
     *
     * @var int
     * @var int $FORM_C
     *
     * @see http://www.unicode.org/glossary/#normalization_form_c
     */
    const NFC = \Normalizer::NFC;

    /**
     * @var int
     *
     * @see NormalizationForms::NFC
     */
    const FORM_C = self::NFC;

    /**
     * Compatibility Decomposition followed by Canonical Composition.
     *
     *    “A normalization form that erases both canonical and compatibility differences,
     *    and generally produces a composed result: for example, the single ǆ character
     *    is converted to d + ž in this form. This form is commonly used in matching.”
     *
     *    -- quoted from unicode glossary linked below
     *
     * @var int
     * @var int $FORM_KC
     *
     * @see http://www.unicode.org/glossary/#normalization_form_kc
     */
    const NFKC = \Normalizer::NFKC;

    /**
     * @var int
     *
     * @see NormalizationForms::NFKC
     */
    const FORM_KC = self::NFKC;

    /**
     * Apple™ Canonical decomposition for HFS Plus filesystems.
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
     *    strings in Mac OS encodings to be converted to Implementation and back without loss of
     *    information. This is not unnatural since a user would not necessarily expect a
     *    dingbat "⒜" to be equivalent to the three character sequence "(a)" in a file name.
     *
     *    The characters in the range u+F900 through u+FAFF are CJK compatibility ideographs,
     *    and are not decomposed in HFS Plus strings.
     *
     *    So, for the example given earlier, u+00E9 ("é") must be stored as the two Implementation
     *    characters u+0065 and u+0301 (in that order). The Implementation character u+00E9 ("é")
     *    may not appear in a Implementation string used as part of an HFS Plus B-tree key.”
     *
     *    -- quoted from Apple™'s Technical Q&A 1150 linked below
     *
     * @var int
     *
     * @see NormalizationForms::NFD
     * @see https://developer.apple.com/library/content/qa/qa1173/_index.html
     * @see https://developer.apple.com/library/content/qa/qa1235/_index.html
     * @see http://dubeiko.com/development/FileSystems/HFSPLUS/tn1150.html#CanonicalDecomposition
     * @see https://opensource.apple.com/source/libiconv/libiconv-50/libiconv/lib/utf8mac.h.auto.html
     */
    const NFD_MAC = 18; // 0x02 (NFD) | 0x10 = 0x12 (18)

    /**
     * @var int
     *
     * @see NormalizationForms::NFD_MAC
     */
    const FORM_D_MAC = self::NFD_MAC;

    /**
     * the supported normalization form values as array of integers.
     *
     * @var int[]
     */
    const NORMALIZATION_FORMS = [
        self::NONE,
        self::NFD,
        self::NFKD,
        self::NFC,
        self::NFKC,
    ];
}
