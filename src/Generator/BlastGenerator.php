<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\CoreBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Sonata\AdminBundle\Model\ModelManagerInterface;

/**
 * Class BlastGenerator.
 */
class BlastGenerator extends Generator
{
    private $file;

    private $modelManager;

    /**
     * @param string $file
     */
    public function __construct($file, ModelManagerInterface $manager, $skeletonDirectories)
    {
        $this->file = (string) $file;
        $this->modelManager = $manager;
        $this->setSkeletonDirs($skeletonDirectories);
    }

    /**
     * @param string $modelClass
     *
     * @throws \RuntimeException
     */
    public function addResource($modelClass)
    {
        $code = '';

        if (is_file($this->file)) {
            $code = rtrim(file_get_contents($this->file));
        }

        $parts = explode('\\', $modelClass);

        $this->renderFile('Blast.yml.twig', $this->file, array(
            'fqcn'    => $modelClass,
            'fields'  => $this->modelManager->getExportFields($modelClass),
            'entity'  => array_pop($parts),
            'oldCode' => $code,
        ));
    }
}
