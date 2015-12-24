<?php

namespace Librinfo\CoreBundle\Twig\Extensions;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;
use Twig_Environment;

class BaseExtension extends \Twig_Extension
{
    /**
     * @var Router
     */
    private $router;

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isObjectInstanceOf', [$this, 'isObjectInstanceOf']),
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
        return (null === $this->router->getRouteCollection()->get($name)) ? false : true;
    }

    /**
     * @param Twig_Environment $twig
     * @param string           $name
     *
     * @return bool
     */
    function isFunctionLoaded(Twig_Environment $twig, $name)
    {
        return $twig->getFunction($name) !== false;
    }

    /**
     * @param Twig_Environment $twig
     * @param string           $name
     *
     * @return bool
     */
    function isExtensionLoaded(Twig_Environment $twig, $name)
    {
        return $twig->hasExtension($name);
    }

    /**
     * @param Twig_Environment $twig
     * @param string           $name
     *
     * @return bool
     */
    function isObjectInstanceOf($object, $class)
    {
        return $object instanceof $class;
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

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }
}
