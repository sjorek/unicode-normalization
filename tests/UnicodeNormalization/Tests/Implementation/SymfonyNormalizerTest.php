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

namespace Sjorek\UnicodeNormalization\Tests\Implementation;

use Sjorek\UnicodeNormalization\NormalizationUtility;
use Sjorek\UnicodeNormalization\Tests\AbstractImplementationTestCase;

/**
 * Testcase for Sjorek\UnicodeNormalization\Implementation\StubNormalizer.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class SymfonyNormalizerTest extends AbstractImplementationTestCase
{
    /**
     * @var string
     */
    const IMPLEMENTATION_CLASS = NormalizationUtility::IMPLEMENTATION_SYMFONY;
}
