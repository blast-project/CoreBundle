<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DefaultParameters.
 */
class DefaultParameters implements ContainerAwareInterface
{
    /**
     * @var
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
     * defineDefaultConfiguration.
     *
     * @param array $parameters
     */
    public function defineDefaultConfiguration(array $parameters)
    {
        foreach ($parameters as $parameterKey => $change) {
            // retrieve current defined parameters
            $containerParameters = $this->container->getParameter($parameterKey);

            // replacing parameter only if defined in yml file
            if (array_key_exists('replace', $change)) {
                // default parameters in an array, so checking its content and replacing matching keys
                if (is_array($containerParameters) && is_array($change['default'])) {
                    foreach ($containerParameters as $defaultKey => $defaultValue) {
                        if (in_array($defaultValue, $change['default'])) {
                            $containerParameters[$defaultKey] = $change['replace'][$defaultKey];
                        }
                    }
                } elseif ($change['default'] == $containerParameters) {
                    // default parameters in a string, simply replacing it
                    $containerParameters = $change['replace'];
                }

                // overriding parameters with our default values
                $this->container->setParameter($parameterKey, $containerParameters);
            }
        }
    }

    /**
     * parameterExists.
     *
     * @param $name
     *
     * @return bool|mixed false if not defined, the parameter if exists
     */
    public function parameterExists($name)
    {
        if ($this->container->hasParameter($name)) {
            return $this->container->getParameter($name);
        } else {
            return false;
        }
    }

    /**
     * @return self
     */
    public static function getInstance(ContainerInterface $container)
    {
        if (self::$instance == null) {
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
