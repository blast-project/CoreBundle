<?php

namespace Librinfo\CoreBundle;

use Librinfo\CoreBundle\DependencyInjection\CodeGeneratorCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LibrinfoCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CodeGeneratorCompilerPass());
    }
}
