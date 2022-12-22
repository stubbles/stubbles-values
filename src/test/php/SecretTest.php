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

use Generator;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
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
 * Base class tests for stubbles\values\Secret.
 *
 * @since 4.0.0
 */
abstract class SecretTest extends TestCase
{
    /**
     * @test
     */
    public function forNullReturnsNullOnUnveil(): void
    {
        assertNull(Secret::forNull()->unveil());
    }

    /**
     * @test
     */
    public function canContainNull(): void
    {
        assertTrue(Secret::forNull()->isContained());
    }

    /**
     * @test
     */
    public function forNullIdentifiesAsNull(): void
    {
        assertTrue(Secret::forNull()->isNull());
    }

    /**
     * @test
     */
    public function lengthOfNullStringIsZero(): void
    {
        assertThat(Secret::forNull()->length(), equals(0));
    }

    /**
     * @test
     */
    public function substringNullStringIsNullString(): void
    {
        assertTrue(Secret::forNull()->substring(2, 33)->isNull());
    }

    public function emptyValues(): Generator
    {
        yield [null];
        yield [''];
    }

    /**
     * @test
     * @dataProvider  emptyValues
     */
    public function createWithEmptyValueThrowsIllegalArgumentException(?string $value): void
    {
        expect(fn() => Secret::create($value))
            ->throws(InvalidArgumentException::class)
            ->withMessage('Given string was null or empty, if you explicitly want to create a Secret with value null use Secret::forNull()');
    }

    /**
     * @test
     */
    public function notSerializable(): void
    {
        expect(fn() => serialize(Secret::create('payload')))
            ->throws(LogicException::class);
    }

    /**
     * @test
     */
    public function varExportNotRevealingPayload(): void
    {
        assertThat(
            var_export(Secret::create('payload'), true),
            doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function varDumpNotRevealingPayload(): void
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assertThat($output, doesNotContain('payload'));
    }

    /**
     * @test
     * @since 4.1.2
     */
    public function varDumpNotRevealingLength(): void
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assertThat($output, doesNotContain('length'));
    }

    /**
     * @test
     */
    public function stringCastNotRevealingPayload(): void
    {
        assertThat(
            (string) Secret::create('payload'),
            doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function arrayCastNotRevealingPayload(): void
    {
        assertThat(
            var_export((array)Secret::create('payload'), true),
            doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function isContainedReturnsTrueWhenEncryptionDoesNotFail(): void
    {
        assertTrue(Secret::create('payload')->isContained());
    }

    /**
     * @test
     */
    public function unveilRevealsOriginalData(): void
    {
        assertThat(Secret::create('payload')->unveil(), equals('payload'));
    }

    /**
     * @test
     */
    public function lengthReturnsStringLengthOfOriginalData(): void
    {
        assertThat(Secret::create('payload')->length(), equals(7));
    }

    /**
     * @test
     */
    public function nonNullSecretDoesNotIdentifyAsNull(): void
    {
        assertFalse(Secret::create('payload')->isNull());
    }

    /**
     * @test
     */
    public function substringWithValidStartReturnsNewInstance(): void
    {
        assertThat(
            Secret::create('payload')->substring(3, 2)->unveil(),
            equals('lo')
        );
    }

    /**
     * @test
     */
    public function substringWithStartOutOfRangeThrowsIllegalArgumentException(): void
    {
        expect(fn() => Secret::create('payload')->substring(50))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function bigData(): void
    {
        $data = str_repeat('*', 1024000);
        assertThat(Secret::create($data)->unveil(), equals($data));
    }

    /**
     * @test
     */
    public function createFromSecretReturnsInstance(): void
    {
        $secret = Secret::create('payload');
        assertThat(Secret::create($secret), isSameAs($secret));
    }

    /**
     * @test
     */
    public function creationNeverThrowsException(): void
    {
        Secret::switchBacking('__none');
        expect(fn() => Secret::create('payload'))
            ->doesNotThrow();
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function creationNeverThrowsError(): void
    {
        Secret::switchBacking('__none_error');
        expect(fn() => Secret::create('payload'))
            ->doesNotThrow();
    }

    /**
     * @test
     */
    public function secretDoesNotContainAnythingWithoutBacking(): void
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        assertFalse($secret->isContained());
    }

    /**
     * @test
     */
    public function unveilThrowsLogicExceptionWhenCreationHasFailed(): void
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        expect(fn() => $secret->unveil())
            ->throws(LogicException::class);
    }

    /**
     * @test
     */
    public function switchToInvalidBackingTypeThrowsIllegalArgumentException(): void
    {
        expect(fn() => Secret::switchBacking('nope'))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function switchBackingWhenSecretInstancesExistThrowsIllegalStateException(): void
    {
        $secret = Secret::create('payload');
        expect(fn() => Secret::switchBacking(Secret::BACKING_PLAINTEXT))
            ->throws(LogicException::class);
    }

    /**
     * @test
     */
    public function canSwitchBackingWhenAllSecretInstancesDestroyed(): void
    {
        $secret = Secret::create('payload');
        $secret = null;
        expect(fn() => Secret::switchBacking(Secret::BACKING_PLAINTEXT))
            ->doesNotThrow();
    }
}
