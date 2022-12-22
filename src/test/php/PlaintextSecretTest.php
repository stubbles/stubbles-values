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
require_once __DIR__ . '/SecretTest.php';
/**
 * Plain text backed tests for stubbles\values\Secret.
 *
 * @since 4.0.0
 * @group values
 * @group secret
 */
class PlaintextSecretTest extends SecretTest
{
    protected function setUp(): void
    {
        Secret::switchBacking(Secret::BACKING_PLAINTEXT);
    }
}
