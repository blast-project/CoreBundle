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

namespace Blast\CoreBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Blast\CoreBundle\DependencyInjection\CodeGeneratorCompilerPass;
use Blast\CoreBundle\DependencyInjection\DashboardBlocksCompilerPass;

class BlastCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CodeGeneratorCompilerPass());
        $container->addCompilerPass(new DashboardBlocksCompilerPass());
    }
}
