<?php

namespace Librinfo\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LibrinfoCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admin.yml');
        $loader->load('config.yml');
        $loader->load('librinfo.yml');

        $configSonataAdmin = Yaml::parse(
            file_get_contents(__DIR__ . '/../Resources/config/bundles/sonata_admin.yml')
        );

        DefaultParameters::getInstance($container)
            ->defineDefaultConfiguration(
                $configSonataAdmin['default']
            )
        ;
    }

    protected function mergeParameter($var, $container, $dir, $file_name = 'librinfo.yml')
    {
        $loader = new Loader\YamlFileLoader($newContainer = new ContainerBuilder(), new FileLocator($dir));
        $loader->load($file_name);
        
        if ( !is_array($container->getParameter($var)) )
        {
            $container->setParameter($var, []);
            return $this;
        }
        if ( !is_array($newContainer->getParameter($var)) )
            return $this;
        
        $container->setParameter($var, array_merge(
            $container->getParameter($var),
            $newContainer->getParameter($var)
        ));
        return $this;
    }

    protected function fixTemplatesConfiguration(array $configs, ContainerBuilder $container, array $defaultSonataDoctrineConfig = [])
    {
        die('glop');
    }
}
