<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Class ArrayToYamlGenerator.
 */
class ArrayToYamlGenerator extends Generator
{
    private $file;

    /**
     * @param string $file
     */
    public function __construct($file, $skeletonDirectories)
    {
        $this->file = $file;
        $this->setSkeletonDirs($skeletonDirectories);
    }

    /**
     * @param string $array
     *
     * @throws \RuntimeException
     */
    public function generate($array, $skeleton)
    {
        $parts = explode('.', $this->file->getPathName());
        array_pop($parts);

        $file = implode('.', $parts) . '.yml';

        if (file_exists($file)) {
            return;
        }

        $this->renderFile($skeleton, $file, array(
            'array' => $array,
        ));
    }
}
