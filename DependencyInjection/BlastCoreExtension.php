<?php

namespace Blast\CoreBundle\DependencyInjection;

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
        
        $this
            ->loadServices($loader)
            ->loadCodeGenerators($container, $config)
            ->loadDataFixtures($container, $loader)
            ->loadParameters($container)
            ->loadSecurity()
            ->loadSonataAdmin($container, $loader)
            ->loadListeners($container, $config)
            ->doLoad($container, $loader, $config)
        ;
    }
    
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
     * the buildLoader returns the required Loader\FileLoader
     * 
     * @return Loader\FileLoader
     **/
    public function buildLoader(ContainerBuilder $container)
    {
        return new Loader\YamlFileLoader($container, new FileLocator($this->dir . $this->prefix));
    }
    
    /**
     * This method is called during the self::load() process, to add the logic related to SonataAdmin
     *
     * @param $loader Loader\FileLoader
     * @return $this
     */
    public function loadServices(Loader\FileLoader $loader)
    {
        // services, admin & config files
        foreach ( ['services', 'admin', 'config'] as $fileName )
        if ( file_exists($this->dir . $this->prefix . $fileName . $this->suffix) )
            $loader->load($fileName . $this->suffix);
        return $this;
    }
    
    /**
     * This method is called after loading the services in the self::load() process, to load code generators
     *
     * @param $container ContainerBuilder
     * @param $config array
     * @return $this
     */
    public function loadCodeGenerators(ContainerBuilder $container, array $config)
    {
        return $this;
    }
    
    /**
     * This method is called after loading the services in the self::load() process, to load data fixtures
     *
     * @param $container ContainerBuilder
     * @param $loader Loader\FileLoader
     * @return $this
     */
    public function loadDataFixtures(ContainerBuilder $container, Loader\FileLoader $loader)
    {
        return $this;
    }
    
    /**
     * This method is called after loading the services in the self::load() process, to load data fixtures
     *
     * @param $container ContainerBuilder
     * @return $this
     */
    public function loadParameters(ContainerBuilder $container)
    {
        // the blast.yml
        if ( file_exists($this->dir . $this->prefix . $this->file . $this->suffix) )
            $this->mergeParameter('blast', $container, $this->dir . $this->prefix);
        return $this;
    }
        
    /**
     * This method is called at the end of the self::load() process, to add logic security related
     *
     * @return $this
     */
    public function loadSecurity()
    {
        return $this;
    }
    
    /**
     * This method is called at the end of the self::load() process, to add the logic related to SonataAdmin
     *
     * @param $container ContainerBuilder
     * @param $loader Loader\FileLoader
     * @return $this
     */
    public function loadSonataAdmin(ContainerBuilder $container, Loader\FileLoader $loader)
    {
        if ( file_exists($path = $this->dir . $this->bundlesPrefix . 'sonata_admin' . $this->suffix) )
        {
            $configSonataAdmin = Yaml::parse(
                file_get_contents($path)
            );

            DefaultParameters::getInstance($container)
                ->defineDefaultConfiguration($configSonataAdmin['default'])
            ;
        }
        return $this;
    }
    
    /**
     * This method is called during the self::load() process, to add the logic related to SonataAdmin
     *
     * @param $container ContainerBuilder
     * @param $config array
     * @return $this
     */
    public function loadListeners(ContainerBuilder $container, array $config)
    {
        return $this;
    }
    
    /**
     * This method is called at the end of the self::load() process, to add any logic needed
     *
     * @param $container ContainerBuilder
     * @param $loader Loader\FileLoader
     * @param $config array
     * @return $this
     */
    public function doLoad(ContainerBuilder $container, Loader\FileLoader $loader, array $config)
    {
        return $this;
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
