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
 * Mcrypt backed tests for stubbles\values\Secret.
 *
 * @since  4.0.0
 * @group  values
 * @group  secret
 * @requires extension  mcrypt
 * @deprecated  since 8.0.0, will be removed with 9.0.0
 */
class McryptSecretTest extends SecretTest
{
    protected function setUp(): void
    {
        if (PHP_MAJOR_VERSION === 7 && PHP_MINOR_VERSION >= 1) {
            $this->markTestSkipped(
                    'Will fail from PHP 7.1 on anyway because of deprecation notice.'
            );
        }

        Secret::switchBacking(Secret::BACKING_MCRYPT);
    }
}
