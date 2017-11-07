<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Dashboard;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

abstract class AbstractDashboardBlock implements ContainerAwareInterface
{
    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $templateParameters = [];

    /**
     * handleParameters should handle $templateParameters. It will be called when
     * the block template will be rendered. Please override this method to set
     * the view parameters of your block.
     */
    public function handleParameters()
    {
        $this->templateParameters = [];
    }

    /**
     * @param string $template
     */
    public function __construct($template = null)
    {
        $this->template = $template;
    }

    /**
     * @return string The content of rendered template
     */
    public function render()
    {
        $this->handleParameters();

        return $this->templating->render($this->template, $this->templateParameters);
    }

    /**
     * @return TwigEngine
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * @param TwigEngine templating
     *
     * @return self
     */
    public function setTemplating(TwigEngine $templating)
    {
        $this->templating = $templating;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface container
     *
     * @return self
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string template
     *
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateParameters()
    {
        return $this->templateParameters;
    }

    /**
     * @param string templateParameters
     *
     * @return self
     */
    public function setTemplateParameters($templateParameters)
    {
        $this->templateParameters = $templateParameters;

        return $this;
    }
}
