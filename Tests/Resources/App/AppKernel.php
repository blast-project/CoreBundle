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

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /*
     * @todo check what we need to register or not
     * @todo find a way to load this only on Functional test
     * @todo check what need to main composer.json
     */

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            // new Symfony\Bundle\MonologBundle\MonologBundle(),
            // new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            // new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // Sonata
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            // new Sonata\IntlBundle\SonataIntlBundle(),

            // Blast
            new Blast\CoreBundle\BlastCoreBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/BlastCoreBundle/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/BlastCoreBundle/logs';
    }
}
