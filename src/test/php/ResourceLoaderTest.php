<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\values;
use bovigo\callmap\NewInstance;
use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\streams\file\FileInputStream;

use function bovigo\assert\{
    assertThat,
    assertEmptyArray,
    assertTrue,
    expect,
    fail,
    predicate\contains,
    predicate\equals,
    predicate\isInstanceOf
};
/**
 * Tests for stubbles\values\ResourceLoader.
 *
 * @since 1.6.0
 */
#[Group('app')]
class ResourceLoaderTest extends TestCase
{
    private ResourceLoader $resourceLoader;

    protected function setUp(): void
    {
        $this->resourceLoader = new ResourceLoader();
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function openNonExistingResourceThrowsDomainException(): void
    {
        expect(fn() => $this->resourceLoader->open('lang/doesNotExist.ini'))
            ->throws(DomainException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadNonExistingResourceThrowsDomainException(): void
    {
        expect(fn() => $this->resourceLoader->load('lang/doesNotExist.ini'))
            ->throws(DomainException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function openLocalResourceReturnsInputStream(): void
    {
        assertThat(
            $this->resourceLoader->open('lang/stubbles.ini'),
            isInstanceOf(FileInputStream::class)
        );
    }

    /**
     * @since 7.0.0
     */
    #[Test]
    public function openLocalResourceWithOtherUsesOther(): void
    {
        $myClass = NewInstance::classname(FileInputStream::class);
        assertThat(
            $this->resourceLoader->open('lang/stubbles.ini', $myClass),
            isInstanceOf($myClass)
        );
    }

    /**
     * @since 7.0.0
     */
    #[Test]
    public function openLocalResourceWithNonExistingClassThrowsInvalidArgumentException(): void
    {
        expect(fn() => $this->resourceLoader->open('lang/stubbles.ini', 'DoesNotExist'))
            ->throws(InvalidArgumentException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadLocalResourceWithoutLoaderReturnsContent(): void
    {
        assertThat(
            $this->resourceLoader->load('lang/stubbles.ini'),
            equals("[foo]\nbar=\"baz\"\n")
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadLocalResourceWithLoaderReturnsLoaderResult(): void
    {
        assertThat(
            $this->resourceLoader->loadWith(
                'lang' . DIRECTORY_SEPARATOR . 'stubbles.ini',
                function(string $resource): string
                {
                    $rootpath = new Rootpath();
                    assertThat(
                            $rootpath->to('src', 'main', 'resources', 'lang', 'stubbles.ini'),
                            equals($resource)
                    );
                    return 'foo';
                }
            ),
            equals('foo')
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function openResourceWithCompletePathInRootReturnsInputStream(): void
    {
        assertThat(
            $this->resourceLoader->open(__FILE__),
            isInstanceOf(FileInputStream::class)
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadResourceWithCompletePathInRootWithoutLoaderReturnsContent(): void
    {
        assertThat(
            $this->resourceLoader->load(__FILE__),
            contains('loadResourceWithCompletePathInRootReturnsContent()')
        );
    }

    /**
     * @since  4.0.0
     */
    #[Test]
    public function loadLocalWithCompletePathWithLoaderReturnsLoaderResult(): void
    {
        $rootpath = new Rootpath();
        assertThat(
            $this->resourceLoader->loadWith(
                $rootpath->to('src', 'main', 'resources', 'lang', 'stubbles.ini'),
                function(string $resource) use($rootpath): string
                {
                    assertThat(
                            $rootpath->to('src', 'main', 'resources', 'lang', 'stubbles.ini'),
                            equals($resource)
                    );
                    return 'foo';
                }
            ),
            equals('foo')
        );
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function openResourceWithCompletePathOutsideRootThrowsDomainException(): void
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test.txt');
        if (false === $tmpName) {
            fail('Could not create temporary filename');
        }

        expect(fn() => $this->resourceLoader->open($tmpName))
            ->throws(DomainException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadResourceWithCompletePathOutsideRootThrowsDomainException(): void
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test.txt');
        if (false === $tmpName) {
            fail('Could not create temporary filename');
        }

        expect(fn() => $this->resourceLoader->load($tmpName))
            ->throws(DomainException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function openResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException(): void
    {
        $resourceLoader = new ResourceLoader(__DIR__);
        expect(fn() => $resourceLoader->open(__DIR__ . '/../../main/php/ResourceLoader.php'))
            ->throws(OutOfBoundsException::class);
    }

    /**
     * @since 4.0.0
     */
    #[Test]
    public function loadResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException(): void
    {
        $resourceLoader = new ResourceLoader(__DIR__);
        expect(fn() => $resourceLoader->load(__DIR__ . '/../../main/php/ResourceLoader.php'))
            ->throws(OutOfBoundsException::class);
    }

    #[Test]
    public function returnsListOfAllResourceUrisForExistingFile(): void
    {
        assertThat(
            $this->resourceLoader->availableResourceUris('lang' . DIRECTORY_SEPARATOR . 'stubbles.ini'),
            equals([
                (new Rootpath()) . DIRECTORY_SEPARATOR
                . 'src' . DIRECTORY_SEPARATOR
                . 'main' . DIRECTORY_SEPARATOR
                . 'resources' . DIRECTORY_SEPARATOR
                . 'lang' . DIRECTORY_SEPARATOR . 'stubbles.ini'
            ])
        );
    }

    #[Test]
    public function returnsEmptyListOfAllResourceUrisForNonExistingFile(): void
    {
        assertEmptyArray(
            $this->resourceLoader->availableResourceUris('doesnot.exist')
        );
    }
}
