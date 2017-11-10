<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sonata\AdminBundle\Generator\ControllerGenerator as BaseControllerGenerator;

/**
 * Class ControllerGenerator.
 */
class ControllerGenerator extends BaseControllerGenerator
{
    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $file;

    /**
     * @param array|string $skeletonDirectory
     */
    public function __construct($skeletonDirectory)
    {
        $this->setSkeletonDirs($skeletonDirectory);
    }

    /**
     * @param BundleInterface $bundle
     * @param string          $controllerClassBasename
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $controllerClassBasename)
    {
        $this->class = sprintf('%s\Controller\%s', $bundle->getNamespace(), $controllerClassBasename);
        $this->file = sprintf(
            '%s/Controller/%s.php',
            $bundle->getPath(),
            str_replace('\\', '/', $controllerClassBasename)
        );
        $parts = explode('\\', $this->class);

        if (file_exists($this->file)) {
            throw new \RuntimeException(sprintf(
                'Unable to generate the admin controller class "%s". The file "%s" already exists.',
                $this->class,
                realpath($this->file)
            ));
        }

        $this->renderFile('AdminController.php.twig', $this->file, array(
            'classBasename' => array_pop($parts),
            'namespace'     => implode('\\', $parts),
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
