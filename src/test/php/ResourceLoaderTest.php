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
 * @since  1.6.0
 * @group  app
 */
class ResourceLoaderTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\values\ResourceLoader
     */
    private $resourceLoader;

    protected function setUp(): void
    {
        $this->resourceLoader = new ResourceLoader();
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function openNonExistingResourceThrowsDomainException(): void
    {
        expect(function() {
                $this->resourceLoader->open('lang/doesNotExist.ini');
        })
        ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadNonExistingResourceThrowsDomainException(): void
    {
        expect(function() {
                $this->resourceLoader->load('lang/doesNotExist.ini');
        })
        ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function openLocalResourceReturnsInputStream(): void
    {
        assertThat(
                $this->resourceLoader->open('lang/stubbles.ini'),
                isInstanceOf(FileInputStream::class)
        );
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function openLocalResourceWithOtherUsesOther(): void
    {
        $myClass = NewInstance::classname(FileInputStream::class);
        assertThat(
                $this->resourceLoader->open('lang/stubbles.ini', $myClass),
                isInstanceOf($myClass)
        );
    }

    /**
     * @test
     * @since  7.0.0
     */
    public function openLocalResourceWithNonExistingClassThrowsInvalidArgumentException(): void
    {
        expect(function() {
                $this->resourceLoader->open('lang/stubbles.ini', 'DoesNotExist');
        })
        ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadLocalResourceWithoutLoaderReturnsContent(): void
    {
        assertThat(
                $this->resourceLoader->load('lang/stubbles.ini'),
                equals("[foo]\nbar=\"baz\"\n")
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
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
     * @test
     * @since  4.0.0
     */
    public function openResourceWithCompletePathInRootReturnsInputStream(): void
    {
        assertThat(
                $this->resourceLoader->open(__FILE__),
                isInstanceOf(FileInputStream::class)
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadResourceWithCompletePathInRootWithoutLoaderReturnsContent(): void
    {
        assertThat(
                $this->resourceLoader->load(__FILE__),
                contains('loadResourceWithCompletePathInRootReturnsContent()')
        );
    }

    /**
     * @test
     * @since  4.0.0
     */
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
     * @test
     * @since  4.0.0
     */
    public function openResourceWithCompletePathOutsideRootThrowsDomainException(): void
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test.txt');
        if (false === $tmpName) {
            fail('Could not create temporary filename');
        }

        expect(function() use ($tmpName) { $this->resourceLoader->open($tmpName); })
            ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadResourceWithCompletePathOutsideRootThrowsDomainException(): void
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test.txt');
        if (false === $tmpName) {
            fail('Could not create temporary filename');
        }

        expect(function() use ($tmpName) { $this->resourceLoader->load($tmpName); })
            ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function openResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException(): void
    {
        expect(function() {
                $resourceLoader = new ResourceLoader(__DIR__);
                $resourceLoader->open(__DIR__ . '/../../main/php/ResourceLoader.php');
        })
        ->throws(\OutOfBoundsException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException(): void
    {
        expect(function() {
                $resourceLoader = new ResourceLoader(__DIR__);
                $resourceLoader->load(__DIR__ . '/../../main/php/ResourceLoader.php');
        })
        ->throws(\OutOfBoundsException::class);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function returnsEmptyListOfAllResourceUrisForNonExistingFile(): void
    {
        assertEmptyArray(
                $this->resourceLoader->availableResourceUris('doesnot.exist')
        );
    }
}
