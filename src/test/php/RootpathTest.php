<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
 * @since 4.0.0
 */
#[Group('resources')]
class RootpathTest extends TestCase
{
    #[Test]
    public function constructWithoutArgumentCalculatesRootpathAutomatically(): void
    {
        assertThat(
            (string) new Rootpath(),
            equals(realpath(__DIR__ . '/../../../'))
        );
    }

    #[Test]
    public function constructWithNonExistingPathThrowsIllegalArgumentException(): void
    {
        expect(fn() => new Rootpath(__DIR__ . '/doesNotExist'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function constructWithExistingPath(): void
    {
        assertThat((string) new Rootpath(__DIR__), equals(__DIR__));
    }

    #[Test]
    public function constructWithExistingPathTurnsDotsIntoRealpath(): void
    {
        assertThat(
            (string) new Rootpath(__DIR__ . '/..'),
            equals(dirname(__DIR__))
        );
    }

    #[Test]
    public function constructWithVfsStreamUriDoesNotApplyRealpath(): void
    {
        $root = vfsStream::setup()->url();
        assertThat((string) new Rootpath($root), equals($root));
    }

    #[Test]
    public function castFromInstanceReturnsInstance(): void
    {
        $rootpath = new Rootpath();
        assertThat(Rootpath::castFrom($rootpath), isSameAs($rootpath));
    }

    #[Test]
    public function castFromWithoutArgumentCalculatesRootpathAutomatically(): void
    {
        assertThat(
            (string) Rootpath::castFrom(null),
            equals(realpath(__DIR__ . '/../../../'))
        );
    }

    #[Test]
    public function castFromWithNonExistingPathThrowsIllegalArgumentException(): void
    {
        expect(fn() => Rootpath::castFrom(__DIR__ . '/doesNotExist'))
            ->throws(InvalidArgumentException::class);
    }

    #[Test]
    public function castFromWithExistingPath(): void
    {
        assertThat((string) Rootpath::castFrom(__DIR__), equals(__DIR__));
    }

    #[Test]
    public function toCreatesPath(): void
    {
        assertThat(
            (string) Rootpath::castFrom(null)
                ->to('src', 'test', 'php', 'RootpathTest.php'),
            equals(__FILE__)
        );
    }

    #[Test]
    public function doesNotContainNonExistingPath(): void
    {
        assertFalse(
            Rootpath::castFrom(null)->contains(__DIR__ . '/doesNotExist')
        );
    }

    #[Test]
    public function doesNotContainPathOutsideRoot(): void
    {
        assertFalse(
            Rootpath::castFrom(__DIR__)->contains(dirname(__DIR__))
        );
    }

    #[Test]
    public function containsPathInsideRoot(): void
    {
        assertTrue(
            Rootpath::castFrom(__DIR__)->contains(__FILE__)
        );
    }

    #[Test]
    public function listOfSourcePathesIsEmptyIfNoAutoloaderPresent(): void
    {
        assertEmptyArray(Rootpath::castFrom(__DIR__)->sourcePathes());
    }

    /**
     * returns path to test resources
     */
    private function rootpathToTestResources(string $last): Rootpath
    {
        return Rootpath::castFrom(
            (new Rootpath())
                ->to('src', 'test', 'resources', 'rootpath', $last)
        );
    }

    #[Test]
    public function listOfSourcePathesWorksWithPsr0Only(): void
    {
        $rootpath = $this->rootpathToTestResources('psr0');
        assertThat(
            $rootpath->sourcePathes(),
            equals([
                $rootpath->to(
                    'vendor' . DIRECTORY_SEPARATOR . 'mikey179'
                    . DIRECTORY_SEPARATOR . 'vfsStream'
                    . DIRECTORY_SEPARATOR . 'src'
                    . DIRECTORY_SEPARATOR . 'main'
                    . DIRECTORY_SEPARATOR . 'php'
                ),
                $rootpath->to(
                    'vendor' . DIRECTORY_SEPARATOR . 'symfony' . DIRECTORY_SEPARATOR . 'yaml'
                )
            ])
        );
    }

    #[Test]
    public function listOfSourcePathesWorksWithPsr4Only(): void
    {
        $rootpath = $this->rootpathToTestResources('psr4');
        assertThat(
            $rootpath->sourcePathes(),
            equals([
                $rootpath->to(
                    'vendor' . DIRECTORY_SEPARATOR . 'stubbles'
                    . DIRECTORY_SEPARATOR . 'core-dev'
                    . DIRECTORY_SEPARATOR . 'src'
                    . DIRECTORY_SEPARATOR . 'main'
                    . DIRECTORY_SEPARATOR . 'php'
                ),
                $rootpath->to(
                    'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php'
                )
            ])
        );
    }

    #[Test]
    public function listOfSourcePathesContainsPsr0AndPsr4(): void
    {
        $rootpath = $this->rootpathToTestResources('all');
        assertThat(
            $rootpath->sourcePathes(),
            equals([
                $rootpath->to(
                    'vendor'
                    . DIRECTORY_SEPARATOR . 'mikey179'
                    . DIRECTORY_SEPARATOR . 'vfsStream'
                    . DIRECTORY_SEPARATOR . 'src'
                    . DIRECTORY_SEPARATOR . 'main'
                    . DIRECTORY_SEPARATOR . 'php'
                ),
                $rootpath->to(
                    'vendor' . DIRECTORY_SEPARATOR . 'symfony' . DIRECTORY_SEPARATOR . 'yaml'
                ),
                $rootpath->to(
                    'vendor'
                    . DIRECTORY_SEPARATOR . 'stubbles'
                    . DIRECTORY_SEPARATOR . 'core-dev'
                    . DIRECTORY_SEPARATOR . 'src'
                    . DIRECTORY_SEPARATOR . 'main'
                    . DIRECTORY_SEPARATOR . 'php'
                ),
                $rootpath->to(
                    'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php'
                )
            ])
        );
    }

    /**
     * @sinces 8.1.0
     */
    #[Test]
    public function defaultRootpathReturnsAutomaticallyCalculatedRootpath(): void
    {
        assertThat(Rootpath::default(), equals(realpath(__DIR__ . '/../../../')));
    }

    /**
     * @since 8.1.0
     */
    #[Test]
    public function defaultRootpathIsAlwaysTheSame(): void
    {
        assertThat(Rootpath::default(), equals(Rootpath::default()));
    }
}
