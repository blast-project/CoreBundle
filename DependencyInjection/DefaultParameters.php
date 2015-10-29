<?php

namespace Librinfo\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultParameters
 *
 * @package Librinfo\CoreBundle\DependencyInjection
 */
class DefaultParameters implements ContainerAwareInterface
{
    /**
     * @var $this
     */
    private static $instance;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    private function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * defineDefaultConfiguration
     *
     * @param array $parameters
     */
    public function defineDefaultConfiguration(Array $parameters)
    {
        foreach ($parameters as $parameterKey => $change)
        {
            // retrieve current defined parameters
            $containerParameters = $this->container->getParameter($parameterKey);

            // replacing parameter only if defined in yml file
            if (array_key_exists('replace', $change))
            {
                // default parameters in an array, so checking its content and replacing matching keys
                if (is_array($containerParameters) && is_array($change['default']))
                {
                    foreach ($containerParameters as $defaultKey => $defaultValue)
                        if (in_array($defaultValue, $change['default']))
                            $containerParameters[$defaultKey] = $change['replace'][$defaultKey];
                }
                else // default parameters in a string, simply replacing it
                    if ($change['default'] == $containerParameters)
                        $containerParameters = $change['replace'];

                // overriding parameters with our default values
                $this->container->setParameter($parameterKey, $containerParameters);
            }
        }
    }

    /**
     * @return mixed
     */
    public static function getInstance(ContainerInterface $container)
    {
        if (self::$instance == null)
        {
            self::$instance = new self($container);
        }

        return self::$instance;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

}
