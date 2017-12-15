<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\FileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class BlastCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $this->initialize();
        $loader = $this->buildLoader($container);

        // $this->loadParameters($container, $loader);
        $this->loadServices($loader);
        $this->loadCodeGenerators($container, $config);
        $this->loadDataFixtures($container, $loader);
        $this->loadBlasts($container, $loader);
        $this->loadSecurity($container);
        $this->loadSonataAdmin($container, $loader);
        $this->loadListeners($container, $config);
        $this->doLoad($container, $loader, $config);
    }

    /**
     * @return self
     */
    public function initialize()
    {
        $rc = new \ReflectionClass($this);
        $this->dir = dirname($rc->getFileName());
        $this->prefix = '/../Resources/config/';
        $this->bundlesPrefix = $this->prefix . 'bundles/';
        $this->suffix = '.yml';
        $this->file = 'blast';

        return $this;
    }

    /**
     * the buildLoader returns the required FileLoader.
     *
     * @param ContainerBuilder $container
     *
     * @return FileLoader
     */
    public function buildLoader(ContainerBuilder $container)
    {
        return new YamlFileLoader($container, new FileLocator($this->dir . $this->prefix));
    }

    /**
     * This method is called during the self::load() process, to add the logic related to SonataAdmin.
     *
     * @param FileLoader $loader
     *
     * @return self
     */
    public function loadServices(FileLoader $loader)
    {
        if (file_exists($this->dir . $this->prefix . 'services' . $this->suffix)) {
            $loader->load('services' . $this->suffix);
        }

        return $this;
    }

    /**
     * This method is called after loading the services in the self::load() process, to load code generators.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return self
     */
    public function loadCodeGenerators(ContainerBuilder $container, array $config)
    {
        return $this;
    }

    /**
     * This method is called after loading the services in the self::load() process, to load data fixtures.
     *
     * @param ContainerBuilder $container
     * @param FileLoader       $loader
     *
     * @return self
     */
    public function loadDataFixtures(ContainerBuilder $container, FileLoader $loader)
    {
        return $this;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return self
     */
    public function loadBlasts(ContainerBuilder $container, FileLoader $loader)
    {
        // the blast.yml
        if (file_exists($this->dir . $this->prefix . $this->file . $this->suffix)) {
            $this->mergeParameter('blast', $container, $this->dir . $this->prefix);
        }

        return $this;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return self
     */
    public function loadParameters(ContainerBuilder $container, FileLoader $loader)
    {
        // parameters.yml
        if (file_exists($this->dir . $this->prefix . 'parameters' . $this->suffix)) {
            $loader->load('parameters' . $this->suffix);
        }

        return $this;
    }

    /**
     * This method is called at the end of the self::load() process, to add security related logic.
     *
     * @param ContainerBuilder $container
     *
     * @return self
     */
    public function loadSecurity(ContainerBuilder $container)
    {
        return $this;
    }

    /**
     * This method is called at the end of the self::load() process, to add the logic related to SonataAdmin.
     *
     * @param ContainerBuilder $container
     * @param FileLoader       $loader
     *
     * @return self
     */
    public function loadSonataAdmin(ContainerBuilder $container, FileLoader $loader)
    {
        if (file_exists($path = $this->dir . $this->bundlesPrefix . 'sonata_admin' . $this->suffix)) {
            $configSonataAdmin = Yaml::parse(
                file_get_contents($path)
            );

            DefaultParameters::getInstance($container)->defineDefaultConfiguration($configSonataAdmin['default']);
        }

        return $this;
    }

    /**
     * This method is called during the self::load() process, to add the logic related to SonataAdmin.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return self
     */
    public function loadListeners(ContainerBuilder $container, array $config)
    {
        return $this;
    }

    /**
     * This method is called at the end of the self::load() process, to add any logic needed.
     *
     * @param ContainerBuilder $container
     * @param FileLoader       $loader
     * @param array            $config
     *
     * @return self
     */
    public function doLoad(ContainerBuilder $container, FileLoader $loader, array $config)
    {
        return $this;
    }

    /**
     * @param string           $var       the parameter name
     * @param ContainerBuilder $container
     * @param string           $dir
     * @param string           $file_name
     *
     * @return self
     */
    protected function mergeParameter($var, $container, $dir, $file_name = 'blast.yml')
    {
        $loader = new YamlFileLoader($newContainer = new ContainerBuilder(), new FileLocator($dir));
        $loader->load($file_name);

        if (!$container->hasParameter($var) || !is_array($container->getParameter($var))) {
            $container->setParameter($var, []);
        }

        if (!$newContainer->hasParameter($var) || !is_array($newContainer->getParameter($var))) {
            return $this;
        }

        $container->setParameter($var, array_replace_recursive(
            $container->getParameter($var),
            $newContainer->getParameter($var)
        ));

        return $this;
    }
}
