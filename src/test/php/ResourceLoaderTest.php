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
     * @type  \stubbles\values\ResourceLoader
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
    public function openNonExistingResourceThrowsDomainException()
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
    public function loadNonExistingResourceThrowsDomainException()
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
    public function openLocalResourceReturnsInputStream()
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
    public function openLocalResourceWithOtherUsesOther()
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
    public function openLocalResourceWithNonExistingClassThrowsInvalidArgumentException()
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
    public function loadLocalResourceWithoutLoaderReturnsContent()
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
    public function loadLocalResourceWithLoaderReturnsLoaderResult()
    {
        assertThat(
                $this->resourceLoader->load(
                        'lang/stubbles.ini',
                        function($resource)
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
    public function openResourceWithCompletePathInRootReturnsInputStream()
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
    public function loadResourceWithCompletePathInRootWithoutLoaderReturnsContent()
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
    public function loadLocalWithCompletePathWithLoaderReturnsLoaderResult()
    {
        $rootpath = new Rootpath();
        assertThat(
                $this->resourceLoader->load(
                        $rootpath->to('src', 'main', 'resources', 'lang', 'stubbles.ini'),
                        function($resource) use($rootpath)
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
    public function openResourceWithCompletePathOutsideRootThrowsDomainException()
    {
        expect(function() {
                $this->resourceLoader->open(tempnam(sys_get_temp_dir(), 'test.txt'));
        })
        ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function loadResourceWithCompletePathOutsideRootThrowsDomainException()
    {
        expect(function() {
                $this->resourceLoader->load(tempnam(sys_get_temp_dir(), 'test.txt'));
        })
        ->throws(\DomainException::class);
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function openResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException()
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
    public function loadResourceWithCompleteRealpathOutsideRootThrowsOutOfBoundsException()
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
    public function returnsListOfAllResourceUrisForExistingFile()
    {
        assertThat(
                $this->resourceLoader->availableResourceUris('lang/stubbles.ini'),
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
    public function returnsEmptyListOfAllResourceUrisForNonExistingFile()
    {
        assertEmptyArray(
                $this->resourceLoader->availableResourceUris('doesnot.exist')
        );
    }
}
