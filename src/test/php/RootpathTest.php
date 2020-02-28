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
    public function constructWithoutArgumentCalculatesRootpathAutomatically(): void
    {
        assertThat(
                (string) new Rootpath(),
                equals(realpath(__DIR__ . '/../../../'))
        );
    }

    /**
     * @test
     */
    public function constructWithNonExistingPathThrowsIllegalArgumentException(): void
    {
        expect(function() {
                new Rootpath(__DIR__ . '/doesNotExist');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function constructWithExistingPath(): void
    {
        assertThat((string) new Rootpath(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function constructWithExistingPathTurnsDotsIntoRealpath(): void
    {
        assertThat(
                (string) new Rootpath(__DIR__ . '/..'),
                equals(dirname(__DIR__))
        );
    }

    /**
     * @test
     */
    public function constructWithVfsStreamUriDoesNotApplyRealpath(): void
    {
        $root = vfsStream::setup()->url();
        assertThat((string) new Rootpath($root), equals($root));
    }

    /**
     * @test
     */
    public function castFromInstanceReturnsInstance(): void
    {
        $rootpath = new Rootpath();
        assertThat(Rootpath::castFrom($rootpath), isSameAs($rootpath));
    }

     /**
     * @test
     */
    public function castFromWithoutArgumentCalculatesRootpathAutomatically(): void
    {
        assertThat(
                (string) Rootpath::castFrom(null),
                equals(realpath(__DIR__ . '/../../../'))
        );
    }

    /**
     * @test
     */
    public function castFromWithNonExistingPathThrowsIllegalArgumentException(): void
    {
        expect(function() {
                Rootpath::castFrom(__DIR__ . '/doesNotExist');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function castFromWithExistingPath(): void
    {
        assertThat((string) Rootpath::castFrom(__DIR__), equals(__DIR__));
    }

    /**
     * @test
     */
    public function toCreatesPath(): void
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
    public function doesNotContainNonExistingPath(): void
    {
        assertFalse(
                Rootpath::castFrom(null)->contains(__DIR__ . '/doesNotExist')
        );
    }

    /**
     * @test
     */
    public function doesNotContainPathOutsideRoot(): void
    {
        assertFalse(
                Rootpath::castFrom(__DIR__)->contains(dirname(__DIR__))
        );
    }

    /**
     * @test
     */
    public function containsPathInsideRoot(): void
    {
        assertTrue(
                Rootpath::castFrom(__DIR__)->contains(__FILE__)
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesIsEmptyIfNoAutoloaderPresent(): void
    {
        assertEmptyArray(Rootpath::castFrom(__DIR__)->sourcePathes());
    }

    /**
     * returns path to test resources
     *
     * @param   string  $last
     * @return  \stubbles\values\Rootpath
     */
    private function rootpathToTestResources($last): Rootpath
    {
        return Rootpath::castFrom(
                (new Rootpath())
                        ->to('src', 'test', 'resources', 'rootpath', $last)
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesWorksWithPsr0Only(): void
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
    public function listOfSourcePathesWorksWithPsr4Only(): void
    {
        $rootpath = $this->rootpathToTestResources('psr4');
        assertThat(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/stubbles/core-dev/src/main/php'),
                        $rootpath->to('src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php')
                ])
        );
    }

    /**
     * @test
     */
    public function listOfSourcePathesContainsPsr0AndPsr4(): void
    {
        $rootpath = $this->rootpathToTestResources('all');
        assertThat(
                $rootpath->sourcePathes(),
                equals([
                        $rootpath->to('vendor/mikey179/vfsStream/src/main/php'),
                        $rootpath->to('vendor/symfony/yaml'),
                        $rootpath->to('vendor/stubbles/core-dev/src/main/php'),
                        $rootpath->to('src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php')
                ])
        );
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function defaultRootpathReturnsAutomaticallyCalculatedRootpath(): void
    {
        assertThat(Rootpath::default(), equals(realpath(__DIR__ . '/../../../')));
    }

    /**
     * @test
     * @since  8.1.0
     */
    public function defaultRootpathIsAlwaysTheSame(): void
    {
        assertThat(Rootpath::default(), equals(Rootpath::default()));
    }
}
