<?php

namespace Blast\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Blast\CoreBundle\DependencyInjection\CodeGeneratorCompilerPass;

class BlastCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CodeGeneratorCompilerPass());
    }
}
