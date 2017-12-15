<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Generator;

use Sonata\AdminBundle\Model\ModelManagerInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sonata\AdminBundle\Generator\AdminGenerator as BaseAdminGenerator;

class AdminGenerator extends BaseAdminGenerator
{
    /**
     * @var ModelManagerInterface
     */
    private $modelManager;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $file;

    /**
     * @param ModelManagerInterface $modelManager
     * @param array|string          $skeletonDirectories
     */
    public function __construct(ModelManagerInterface $modelManager, $skeletonDirectories)
    {
        $this->modelManager = $modelManager;
        $this->setSkeletonDirs($skeletonDirectories);
    }

    /**
     * @param BundleInterface $bundle
     * @param string          $adminClassBasename
     * @param string          $modelClass
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $adminClassBasename, $modelClass)
    {
        $this->class = sprintf('%s\Admin\%s', $bundle->getNamespace(), $adminClassBasename);
        $this->file = sprintf('%s/Admin/%s.php', $bundle->getPath(), str_replace('\\', '/', $adminClassBasename));
        $parts = explode('\\', $this->class);

        if (file_exists($this->file)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the admin class "%s". The file "%s" already exists.',
                $this->class,
                realpath($this->file)
            ));
        }

        // Manage route pattern generation
        $routes = $parts;
        array_walk($routes, function (&$item, $k) use (&$routes) {
            $item = preg_replace('/(admin)|(bundle)/', '', strtolower($item));
            if ($item == '') {
                array_splice($routes, $k, 1);
            }
        });

        $this->renderFile('Admin.php.twig', $this->file, array(
            'route_name'    => implode('_', array_map('strtolower', $routes)),
            'classBasename' => array_pop($parts),
            'namespace'     => implode('\\', $parts),
            'fields'        => $this->modelManager->getExportFields($modelClass),
        ));
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getFile()
    {
        return $this->file;
    }
}
