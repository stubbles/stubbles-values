<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles\values
 */
namespace stubbles\values;
/**
 * Class to load resources from arbitrary locations.
 *
 * @since  1.6.0
 * @Singleton
 */
class ResourceLoader
{
    /**
     * root path of application
     *
     * @type  \stubbles\values\Rootpath
     */
    private $rootpath;

    /**
     * constructor
     *
     * If no root path is given it tries to detect it automatically.
     *
     * @param  string|\stubbles\values\Rootpath  $rootpath  optional
     */
    public function __construct($rootpath = null)
    {
        $this->rootpath = Rootpath::castFrom($rootpath);
    }

    /**
     * opens an input stream to read resource contents
     *
     * Resource can either be a complete path to a resource or a local path. In
     * case it is a local path it is searched within the src/main/resources
     * of the current project.
     * It is not possible to open resources outside of the root path by
     * providing a complete path, a complete path must always lead to a resource
     * located within the root path.
     *
     * If no class to open the resource with is specified it will use
     * stubbles\streams\file\FileInputStream. The given class must accept the
     * full path of the resource as first constructor argument, and must not
     * require any other argument.
     *
     * @param   string  $resource   name of resource to open
     * @param   string  $withClass  optional  name of class to open resource with
     * @return  $withClass
     * @throws  \InvalidArgumentException  in case the given class does not exist
     * @since   4.0.0
     */
    public function open($resource, $withClass = 'stubbles\streams\file\FileInputStream')
    {
        if (!class_exists($withClass)) {
            throw new \InvalidArgumentException(
                    'Can not open ' . $resource . ' with ' . $withClass
                    . ', class does not exit.'
            );
        }

        return new $withClass($this->checkedPathFor($resource));
    }

    /**
     * loads resource contents
     *
     * Resource can either be a complete path to a resource or a local path. In
     * case it is a local path it is searched within the src/main/resources
     * of the current project.
     * It is not possible to load resources outside of the root path by
     * providing a complete path, a complete path must always lead to a resource
     * located within the root path.
     * In case no $loader is given the resource will be loaded with
     * file_get_contents(). The given loader must accept a path and return the
     * result from the load operation.
     *
     * @param   string    $resource
     * @param   callable  $loader    optional  code to load resource with, defaults to file_get_contents()
     * @return  mixed     result of call to $loader, or file contents if no loader specified
     * @since   4.0.0
     */
    public function load($resource, callable $loader = null)
    {
        $checkedPath = $this->checkedPathFor($resource);
        if (null == $loader) {
            return file_get_contents($checkedPath);
        }

        return $loader($checkedPath);
    }

    /**
     * completes path for given resource
     *
     * In case the complete path is outside of the root path an
     * IllegalArgumentException is thrown.
     *
     * @param   string  $resource
     * @return  string
     * @throws  \DomainException  in case the given resource does not exist
     * @throws  \OutOfBoundsException  in case the resource is not within rootpath
     */
    private function checkedPathFor($resource)
    {
        $completePath = $this->completePath($resource);
        if (!file_exists($completePath)) {
            throw new \DomainException('Resource ' . $completePath . ' not found');
        }

        if (!$this->rootpath->contains($completePath)) {
            throw new \OutOfBoundsException(
                    'Given resource "' . $resource
                    . '" located at "' . $completePath
                    . '" is not inside root path ' . $this->rootpath
            );
        }

        return $completePath;
    }

    /**
     * returns complete path for given resource
     *
     * @param   string  $resource
     * @return  string
     */
    private function completePath($resource)
    {
        if (substr($resource, 0, strlen($this->rootpath)) == $this->rootpath) {
            return $resource;
        }

        return $this->rootpath
                . DIRECTORY_SEPARATOR . 'src'
                . DIRECTORY_SEPARATOR . 'main'
                . DIRECTORY_SEPARATOR . 'resources'
                . DIRECTORY_SEPARATOR . $resource;
    }

    /**
     * returns a list of all available uris for a resource
     *
     * The returned list is sorted alphabetically, meaning that local resources
     * of the current project are always returned as first entry if they exist,
     * and all vendor resources after. Order of vendor resources is also in
     * alphabetical order of vendor/package names.
     *
     * @param   string  $resourceName  the resource to retrieve the uris for
     * @return  string[]
     * @since   4.0.0
     */
    public function availableResourceUris($resourceName)
    {
        $resourceUris = array_values(
                array_filter(
                        array_map(
                              function($sourcePath) use($resourceName)
                              {
                                  return str_replace('/src/main/php', '/src/main/resources', $sourcePath) . DIRECTORY_SEPARATOR . $resourceName;
                              },
                              $this->rootpath->sourcePathes()
                        ),
                        function($resourcePath)
                        {
                            return file_exists($resourcePath);
                        }
                )
        );
        sort($resourceUris);
        return $resourceUris;
    }
}
