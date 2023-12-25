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

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\Test;

use function bovigo\assert\{
    assertThat,
    assertFalse,
    assertNull,
    assertTrue,
    expect,
    predicate\doesNotContain,
    predicate\equals,
    predicate\isSameAs
};
/**
 * Base tests for stubbles\values\Secret.
 *
 * @since 4.0.0
 */
trait SecretTests
{
    #[Test]
    public function forNullReturnsNullOnUnveil(): void
    {
        assertNull(Secret::forNull()->unveil());
    }

    #[Test]
    public function canContainNull(): void
    {
        assertTrue(Secret::forNull()->isContained());
    }

    #[Test]
    public function forNullIdentifiesAsNull(): void
    {
        assertTrue(Secret::forNull()->isNull());
    }

    #[Test]
    public function lengthOfNullStringIsZero(): void
    {
        assertThat(Secret::forNull()->length(), equals(0));
    }

    #[Test]
    public function createWithEmptyValueThrowsIllegalArgumentException(): void
    {
        expect(fn() => Secret::create(''))
            ->throws(InvalidArgumentException::class)
            ->withMessage('Can not have secret with empty string.');
    }

    #[Test]
    public function notSerializable(): void
    {
        expect(fn() => serialize(Secret::create('payload')))
            ->throws(LogicException::class);
    }

    #[Test]
    public function varExportNotRevealingPayload(): void
    {
        assertThat(
            var_export(Secret::create('payload'), true),
            doesNotContain('payload')
        );
    }

    #[Test]
    public function varDumpNotRevealingPayload(): void
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assertThat($output, doesNotContain('payload'));
    }

    /**
     * @since 4.1.2
     */
    #[Test]
    public function varDumpNotRevealingLength(): void
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assertThat($output, doesNotContain('length'));
    }

    #[Test]
    public function stringCastNotRevealingPayload(): void
    {
        assertThat(
            (string) Secret::create('payload'),
            doesNotContain('payload')
        );
    }

    #[Test]
    public function arrayCastNotRevealingPayload(): void
    {
        assertThat(
            var_export((array)Secret::create('payload'), true),
            doesNotContain('payload')
        );
    }

    #[Test]
    public function isContainedReturnsTrueWhenEncryptionDoesNotFail(): void
    {
        assertTrue(Secret::create('payload')->isContained());
    }

    #[Test]
    public function unveilRevealsOriginalData(): void
    {
        assertThat(Secret::create('payload')->unveil(), equals('payload'));
    }

    #[Test]
    public function lengthReturnsStringLengthOfOriginalData(): void
    {
        assertThat(Secret::create('payload')->length(), equals(7));
    }

    #[Test]
    public function nonNullSecretDoesNotIdentifyAsNull(): void
    {
        assertFalse(Secret::create('payload')->isNull());
    }

    #[Test]
    public function bigData(): void
    {
        $data = str_repeat('*', 1024000);
        assertThat(Secret::create($data)->unveil(), equals($data));
    }

    #[Test]
    public function createFromSecretReturnsInstance(): void
    {
        $secret = Secret::create('payload');
        assertThat(Secret::create($secret), isSameAs($secret));
    }

    #[Test]
    public function creationNeverThrowsException(): void
    {
        Secret::switchBacking('__none');
        expect(fn() => Secret::create('payload'))
            ->doesNotThrow();
    }

    /**
     * @since  8.0.0
     */
    #[Test]
    public function creationNeverThrowsError(): void
    {
        Secret::switchBacking('__none_error');
        expect(fn() => Secret::create('payload'))
            ->doesNotThrow();
    }

    #[Test]
    public function secretDoesNotContainAnythingWithoutBacking(): void
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        assertFalse($secret->isContained());
    }

    #[Test]
    public function unveilThrowsLogicExceptionWhenCreationHasFailed(): void
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        expect(fn() => $secret->unveil())
            ->throws(LogicException::class);
    }

    #[Test]
    public function switchToInvalidBackingTypeThrowsIllegalArgumentException(): void
    {
        expect(fn() => Secret::switchBacking('nope'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function switchBackingWhenSecretInstancesExistThrowsIllegalStateException(): void
    {
        $secret = Secret::create('payload');
        expect(fn() => Secret::switchBacking(Secret::BACKING_PLAINTEXT))
            ->throws(LogicException::class);
    }

    #[Test]
    public function canSwitchBackingWhenAllSecretInstancesDestroyed(): void
    {
        $secret = Secret::create('payload');
        $secret = null;
        expect(fn() => Secret::switchBacking(Secret::BACKING_PLAINTEXT))
            ->doesNotThrow();
    }
}
