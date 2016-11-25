<?php

namespace Blast\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register the entity code generator services
 * (services tagged with "librinfo.entity_code_generator")
 */
class CodeGeneratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('librinfo_core.code_generators'))
            return;

        $registry = $container->findDefinition('librinfo_core.code_generators');

        $taggedServices = $container->findTaggedServiceIds('librinfo.entity_code_generator');

        foreach ($taggedServices as $id => $tags)
            $registry->addMethodCall('register',  [new Reference($id)]);
    }
}