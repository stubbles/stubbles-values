<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\{
    assertThat,
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
class RootpathTest extends TestCase
{
    /**
     * @test
     */
    public function constructWithoutArgumentCalculatesRootpathAutomatically()
    {
        assertThat(
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
        assertThat((string) new Rootpath(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function constructWithExistingPathTurnsDotsIntoRealpath()
    {
        assertThat(
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
        assertThat((string) new Rootpath($root), equals($root));
    }

    /**
     * @test
     */
    public function castFromInstanceReturnsInstance()
    {
        $rootpath = new Rootpath();
        assertThat(Rootpath::castFrom($rootpath), isSameAs($rootpath));
    }

     /**
     * @test
     */
    public function castFromWithoutArgumentCalculatesRootpathAutomatically()
    {
        assertThat(
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
        assertThat((string) Rootpath::castFrom(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function toCreatesPath()
    {
        assertThat(
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
        assertThat(
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
        assertThat(
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
        assertThat(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/mikey179/vfsStream/src/main/php'),
                        $rootpath->to('vendor/symfony/yaml'),
                        $rootpath->to('vendor/stubbles/core-dev/src/main/php'),
                        $rootpath->to('src/main/php')
                ])
        );
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function defaultRootpathReturnsAutomaticallyCalculatedRootpath()
    {
        assertThat(Rootpath::default(), equals(realpath(__DIR__ . '/../../../')));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function defaultRootpathIsAlwaysTheSame()
    {
        assertThat(Rootpath::default(), equals(Rootpath::default()));
    }
}
