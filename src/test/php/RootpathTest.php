<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\values
 */
namespace stubbles\values;
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\{
    assert,
    assertEmptyArray,
    assertFalse,
    assertTrue,
    expect,
    predicate\equals,
    predicate\isSameAs
};
/**
 * Tests for stubbles\values\Rootpath.
 *
 * @since  4.0.0
 * @group  resources
 */
class RootpathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructWithoutArgumentCalculatesRootpathAutomatically()
    {
        assert(
                (string) new Rootpath(),
                equals(realpath(__DIR__ . '/../../../'))
        );
    }

    /**
     * @test
     */
    public function constructWithNonExistingPathThrowsIllegalArgumentException()
    {
        expect(function() {
                new Rootpath(__DIR__ . '/doesNotExist');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function constructWithExistingPath()
    {
        assert((string) new Rootpath(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function constructWithExistingPathTurnsDotsIntoRealpath()
    {
        assert(
                (string) new Rootpath(__DIR__ . '/..'),
                equals(dirname(__DIR__))
        );
    }

    /**
     * @test
     */
    public function constructWithVfsStreamUriDoesNotApplyRealpath()
    {
        $root = vfsStream::setup()->url();
        assert((string) new Rootpath($root), equals($root));
    }

    /**
     * @test
     */
    public function castFromInstanceReturnsInstance()
    {
        $rootpath = new Rootpath();
        assert(Rootpath::castFrom($rootpath), isSameAs($rootpath));
    }

     /**
     * @test
     */
    public function castFromWithoutArgumentCalculatesRootpathAutomatically()
    {
        assert(
                (string) Rootpath::castFrom(null),
                equals(realpath(__DIR__ . '/../../../'))
        );
    }

    /**
     * @test
     */
    public function castFromWithNonExistingPathThrowsIllegalArgumentException()
    {
        expect(function() {
                Rootpath::castFrom(__DIR__ . '/doesNotExist');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function castFromWithExistingPath()
    {
        assert((string) Rootpath::castFrom(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function toCreatesPath()
    {
        assert(
                (string) Rootpath::castFrom(null)
                        ->to('src', 'test', 'php', 'RootpathTest.php'),
                equals(__FILE__)
        );
    }

    /**
     * @test
     */
    public function doesNotContainNonExistingPath()
    {
        assertFalse(
                Rootpath::castFrom(null)->contains(__DIR__ . '/doesNotExist')
        );
    }

    /**
     * @test
     */
    public function doesNotContainPathOutsideRoot()
    {
        assertFalse(
                Rootpath::castFrom(__DIR__)->contains(dirname(__DIR__))
        );
    }

    /**
     * @test
     */
    public function containsPathInsideRoot()
    {
        assertTrue(
                Rootpath::castFrom(__DIR__)->contains(__FILE__)
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesIsEmptyIfNoAutoloaderPresent()
    {
        assertEmptyArray(Rootpath::castFrom(__DIR__)->sourcePathes());
    }

    /**
     * returns path to test resources
     *
     * @param   string  $last
     * @return  \stubbles\values\Rootpath
     */
    private function rootpathToTestResources($last)
    {
        return Rootpath::castFrom(
                (new Rootpath())
                        ->to('src', 'test', 'resources', 'rootpath', $last)
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesWorksWithPsr0Only()
    {
        $rootpath = $this->rootpathToTestResources('psr0');
        assert(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/mikey179/vfsStream/src/main/php'),
                        $rootpath->to('vendor/symfony/yaml')
                ])
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesWorksWithPsr4Only()
    {
        $rootpath = $this->rootpathToTestResources('psr4');
        assert(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/stubbles/core-dev/src/main/php'),
                        $rootpath->to('src/main/php')
                ])
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesContainsPsr0AndPsr4()
    {
        $rootpath = $this->rootpathToTestResources('all');
        assert(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/mikey179/vfsStream/src/main/php'),
                        $rootpath->to('vendor/symfony/yaml'),
                        $rootpath->to('vendor/stubbles/core-dev/src/main/php'),
                        $rootpath->to('src/main/php')
                ])
        );
    }
}
