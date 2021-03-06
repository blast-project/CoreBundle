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

namespace Blast\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register the entity code generator services
 * (services tagged with "librinfo.entity_code_generator").
 */
class CodeGeneratorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('blast_core.code_generators')) {
            return;
        }

        $registry = $container->findDefinition('blast_core.code_generators');

        $taggedServices = $container->findTaggedServiceIds('blast.entity_code_generator');

        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall('register', [new Reference($id)]);
        }
    }
}
