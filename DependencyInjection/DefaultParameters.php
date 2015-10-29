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
                // replacing parameter only if default value is not changed
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
