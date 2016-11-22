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

        $rc = new \ReflectionClass($this);
        $dir = dirname($rc->getFileName());
        $prefix = '/../Resources/config/';
        $bundlesPrefix = $prefix . 'bundles/';
        $suffix = '.yml';
        $file = 'blast';
        $sonataFile = 'sonata_admin';
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator($dir . $prefix));

        foreach(['services', 'admin', 'config', $file] as $fileName)
        {
            if( file_exists($dir . $prefix . $fileName . $suffix) )
                if( $fileName != $file || $rc->getName() == 'LibrinfoCoreExtension' )
                    $loader->load($fileName . $suffix);
                else
                    $this->mergeParameter('blast', $container, $dir . $prefix);       
        }
        
        if( file_exists($path = $dir . $bundlesPrefix . $sonataFile . $suffix) )
        {
            $configSonataAdmin = Yaml::parse(
                file_get_contents($path)
            ); 

            DefaultParameters::getInstance($container)
                ->defineDefaultConfiguration($configSonataAdmin['default'])
            ;
        }
    }

    protected function mergeParameter($var, $container, $dir, $file_name = 'blast.yml')
    {
        $loader = new Loader\YamlFileLoader($newContainer = new ContainerBuilder(), new FileLocator($dir));
        $loader->load($file_name);
        
        if ( !$container->hasParameter($var) || !is_array($container->getParameter($var)) )
            $container->setParameter($var, []);
        
        if ( !is_array($newContainer->getParameter($var)) )
            return $this;
        
        $container->setParameter($var, array_merge(
            $container->getParameter($var),
            $newContainer->getParameter($var)
        ));
        
        return $this;
    }
 
}
