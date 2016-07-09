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
 *
 * @package  stubbles\values
 */
namespace stubbles\values;
use function bovigo\assert\{
    assert,
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
 * @since  4.0.0
 */
abstract class SecretTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function forNullReturnsNullOnUnveil()
    {
        assertNull(Secret::forNull()->unveil());
    }

    /**
     * @test
     */
    public function canContainNull()
    {
        assertTrue(Secret::forNull()->isContained());
    }

    /**
     * @test
     */
    public function forNullIdentifiesAsNull()
    {
        assertTrue(Secret::forNull()->isNull());
    }

    /**
     * @test
     */
    public function lengthOfNullStringIsZero()
    {
        assert(Secret::forNull()->length(), equals(0));
    }

    /**
     * @test
     */
    public function substringNullStringIsNullString()
    {
        assertTrue(Secret::forNull()->substring(2, 33)->isNull());
    }

    /**
     * @return  array
     */
    public function emptyValues(): array
    {
        return [[null], ['']];
    }

    /**
     * @test
     * @dataProvider  emptyValues
     */
    public function createWithEmptyValueThrowsIllegalArgumentException($value)
    {
        expect(function() use ($value) {
                Secret::create($value);
        })
        ->throws(\InvalidArgumentException::class)
        ->withMessage('Given string was null or empty, if you explicitly want to create a Secret with value null use Secret::forNull()');
    }

    /**
     * @test
     */
    public function notSerializable()
    {
        expect(function() {
                serialize(Secret::create('payload'));
        })
        ->throws(\LogicException::class);
    }

    /**
     * @test
     */
    public function varExportNotRevealingPayload()
    {
        assert(
                var_export(Secret::create('payload'), true),
                doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function varDumpNotRevealingPayload()
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assert($output, doesNotContain('payload'));
    }

    /**
     * @test
     * @since  4.1.2
     */
    public function varDumpNotRevealingLength()
    {
        ob_start();
        var_dump(Secret::create('payload'));
        $output = ob_get_contents();
        ob_end_clean();

        assert($output, doesNotContain('length'));
    }

    /**
     * @test
     */
    public function stringCastNotRevealingPayload() {
        assert(
                (string) Secret::create('payload'),
                doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function arrayCastNotRevealingPayload()
    {
        assert(
                var_export((array)Secret::create('payload'), true),
                doesNotContain('payload')
        );
    }

    /**
     * @test
     */
    public function isContainedReturnsTrueWhenEncryptionDoesNotFail()
    {
        assertTrue(Secret::create('payload')->isContained());
    }

    /**
     * @test
     */
    public function unveilRevealsOriginalData()
    {
        assert(Secret::create('payload')->unveil(), equals('payload'));
    }

    /**
     * @test
     */
    public function lengthReturnsStringLengthOfOriginalData()
    {
        assert(Secret::create('payload')->length(), equals(7));
    }

    /**
     * @test
     */
    public function nonNullSecretDoesNotIdentifyAsNull()
    {
        assertFalse(Secret::create('payload')->isNull());
    }

    /**
     * @test
     */
    public function substringWithValidStartReturnsNewInstance()
    {
        assert(
                Secret::create('payload')->substring(3, 2)->unveil(),
                equals('lo')
        );
    }

    /**
     * @test
     */
    public function substringWithStartOutOfRangeThrowsIllegalArgumentException()
    {
        expect(function() {
                Secret::create('payload')->substring(50);
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function bigData()
    {
        $data = str_repeat('*', 1024000);
        assert(Secret::create($data)->unveil(), equals($data));
    }

    /**
     * @test
     */
    public function createFromSecretReturnsInstance()
    {
        $secret = Secret::create('payload');
        assert(Secret::create($secret), isSameAs($secret));
    }

    /**
     * @test
     */
    public function creationNeverThrowsException()
    {
        Secret::switchBacking('__none');
        expect(function() {
            Secret::create('payload');
        })
        ->doesNotThrow();
    }

    /**
     * @test
     * @since  8.0.0
     */
    public function creationNeverThrowsError()
    {
        Secret::switchBacking('__none_error');
        expect(function() {
            Secret::create('payload');
        })
        ->doesNotThrow();
    }

    /**
     * @test
     */
    public function secretDoesNotContainAnythingWithoutBacking()
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        assertFalse($secret->isContained());
    }

    /**
     * @test
     */
    public function unveilThrowsLogicExceptionWhenCreationHasFailed()
    {
        Secret::switchBacking('__none');
        $secret = Secret::create('payload');
        expect(function() use ($secret) {
                $secret->unveil();
        })
        ->throws(\LogicException::class);
    }

    /**
     * @test
     */
    public function switchToInvalidBackingTypeThrowsIllegalArgumentException()
    {
        expect(function() {
                Secret::switchBacking('nope');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function switchBackingWhenSecretInstancesExistThrowsIllegalStateException()
    {
        $secret = Secret::create('payload');
        expect(function() {
                Secret::switchBacking(Secret::BACKING_PLAINTEXT);
        })
        ->throws(\LogicException::class);
    }

    /**
     * @test
     */
    public function canSwitchBackingWhenAllSecretInstancesDestroyed()
    {
        $secret = Secret::create('payload');
        $secret = null;
        expect(function() {
                assertTrue(Secret::switchBacking(Secret::BACKING_PLAINTEXT));
        })
        ->doesNotThrow();
    }
}
