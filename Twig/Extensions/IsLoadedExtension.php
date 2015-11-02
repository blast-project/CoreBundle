<?php

namespace Librinfo\CoreBundle\Twig\Extensions;

use Twig_Environment;

class IsLoadedExtension extends \Twig_Extension
{
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
        );
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    function isFunctionLoaded(Twig_Environment $twig, $name)
    {
        return $twig->getFunction($name);
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
        return 'librinfo_core_is_loaded_extension';
    }
}
