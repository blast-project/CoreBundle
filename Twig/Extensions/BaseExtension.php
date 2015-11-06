<?php

namespace Librinfo\CoreBundle\Twig\Extensions;

use Symfony\Component\DependencyInjection\Container;
use Twig_Environment;

class BaseExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isExtensionLoaded', [$this, 'isExtensionLoaded'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('isFunctionLoaded', [$this, 'isFunctionLoaded'], ['needs_environment' => true]),
            new \Twig_SimpleFunction('routeExists', [$this, 'routeExists']),
        );
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    function routeExists($name)
    {
        $router = $this->container->get('router');
        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    function isFunctionLoaded(Twig_Environment $twig, $name)
    {
        return $twig->getFunction($name) !== false;
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    function isExtensionLoaded(Twig_Environment $twig, $name)
    {
        return $twig->hasExtension($name);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'librinfo_core_base_extension';
    }
}
