<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * The contents of this file draw heavily from XP Framework
 * https://github.com/xp-framework/xp-framework
 *
 * Copyright (c) 2001-2014, XP-Framework Team
 * All rights reserved.
 * https://github.com/xp-framework/xp-framework/blob/master/core/src/main/php/LICENCE
 */
namespace stubbles\values;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

/**
 * Sodium backed tests for stubbles\values\Secret.
 *
 * @since 10.0.0
 */
#[Group('values')]
#[Group('secret')]
#[RequiresPhpExtension('sodium')]
class SodiumSecretTest extends TestCase
{
    use SecretTests;
    
    protected function setUp(): void
    {
        Secret::switchBacking(Secret::BACKING_SODIUM);
    }
}
