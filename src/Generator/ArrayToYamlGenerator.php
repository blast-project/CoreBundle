<?php

namespace Blast\CoreBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Class ArrayToYamlGenerator
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
        
        if( file_exists($file) )
            return;
        
        $this->renderFile($skeleton, $file, array(
            'array'   => $array
        ));
    }
}
