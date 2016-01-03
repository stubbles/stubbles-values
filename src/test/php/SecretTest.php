<?php
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

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\doesNotContain;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
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
    public function emptyValues()
    {
        return [[null], ['']];
    }

    /**
     * @test
     * @dataProvider  emptyValues
     * @expectedException  InvalidArgumentException
     * @expectedExceptionMessage  Given string was null or empty, if you explicitly want to create a Secret with value null use Secret::forNull()
     */
    public function createWithEmptyValueThrowsIllegalArgumentException($value)
    {
        Secret::create($value);
    }

    /**
     * @test
     * @expectedException  LogicException
     */
    public function notSerializable()
    {
        serialize(Secret::create('payload'));
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
     * @expectedException  InvalidArgumentException
     */
    public function substringWithStartOutOfRangeThrowsIllegalArgumentException()
    {
        Secret::create('payload')->substring(50);
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
        $secureString = Secret::create('payload');
        assert(Secret::create($secureString), isSameAs($secureString));
    }

    /**
     * @test
     */
    public function creationNeverThrowsException()
    {
        Secret::switchBacking('__none');
        try {
            $secureString = Secret::create('payload');
        } catch (\Exception $e) {
            fail('Exception thrown where no exception may be thrown');
        }

        assertFalse($secureString->isContained());
    }

    /**
     * @test
     * @expectedException  LogicException
     */
    public function unveilThrowsIllegalStateExceptionWhenCreationHasFailed()
    {
        Secret::switchBacking('__none');
        try {
            $secureString = Secret::create('payload');
        } catch (\Exception $e) {
            fail('Exception thrown where no exception may be thrown');
        }

        $secureString->unveil();
    }

    /**
     * @test
     * @expectedException  InvalidArgumentException
     */
    public function switchToInvalidBackingTypeThrowsIllegalArgumentException()
    {
        Secret::switchBacking(404);
    }

    /**
     * @test
     * @expectedException  LogicException
     */
    public function switchBackingWhenSecretInstancesExistThrowsIllegalStateException()
    {
        $secureString = Secret::create('payload');
        Secret::switchBacking(Secret::BACKING_PLAINTEXT);
    }

    /**
     * @test
     */
    public function canSwitchBackingWhenAllSecretInstancesDestroyed()
    {
        $secureString = Secret::create('payload');
        $secureString = null;
        assertTrue(Secret::switchBacking(Secret::BACKING_PLAINTEXT));
    }
}
