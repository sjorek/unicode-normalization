<?php declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Implementation;


if (!class_exists(__NAMESPACE__ . '\\NormalizerImpl', false) && class_exists('Normalizer', false))
{
    /**
     * Unicode Normalizer Implementation
     *
     * @author Stephan Jorek <stephan.jorek@gmail.com>
     */
    class NormalizerImpl extends \Normalizer {}

} elseif (class_exists(__NAMESPACE__ . '\\NormalizerImpl', false)) {

    // Nothing to do here …

} else {

    // Register stub normalizer implementation, providing (very limited and minimal) support for NFC and ASCII.
    class_alias(__NAMESPACE__ . '\\NormalizerStub', __NAMESPACE__ . '\\NormalizerImpl', true);
}
