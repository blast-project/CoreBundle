<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DashboardBlocksCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('blast_core.dashboard.registry')) {
            return;
        }

        $registry = $container->findDefinition('blast_core.dashboard.registry');

        $taggedServices = $container->findTaggedServiceIds('blast.dashboard_block');

        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall('registerBlock', [new Reference($id)]);
        }
    }
}
