<?php

namespace Librinfo\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('admin.yml');
        $loader->load('config.yml');
        $loader->load('librinfo.yml');
        
        // specialize things for easy deployments about templates/layouts, giving the possibility to personalize stuff in the app/config/config.yml of the application
        $templates = $container->getParameter('sonata.admin.configuration.templates');
        foreach ( array(
            'layout' => array('SonataAdminBundle::standard_layout.html.twig', 'CoreBundle::standard_layout.html.twig'),
        ) as $key => $change )
        {
            if ( $templates[$key] == $change[0] )
                $templates[$key] = $change[1];
        }
        $container->setParameter('sonata.admin.configuration.templates', $templates);
    }
    
    protected function mergeParameter($var, $container, $dir)
    {
        $loader = new Loader\YamlFileLoader($newContainer = new ContainerBuilder(), new FileLocator($dir));
        $loader->load('librinfo.yml');
        $container->getParameter($var);
        $container->setParameter($var, $librinfo = array_merge(
            $container->getParameter($var),
            $newContainer->getParameter($var)
        ));
        return $this;
    }
}
