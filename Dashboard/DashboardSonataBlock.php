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

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;

class DashboardSonataBlock extends AbstractBlockService
{
    /**
     * @var DashboardBlockRegistry
     */
    private $registry;

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'url'      => false,
            'title'    => 'librinfo.label.dashboard_block_title',
            'template' => 'BlastCoreBundle:Dashboard:mainDashboard.html.twig',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $dashboardBlocks = $this->registry->getRegistredBlocks();

        return $this->renderResponse($blockContext->getTemplate(), array(
            'block_context'    => $blockContext,
            'block'            => $blockContext->getBlock(),
            'dashboard_blocks' => $dashboardBlocks ?: [],
        ), $response);
    }

    /**
     * @param DashboardBlockRegistry registry
     *
     * @return self
     */
    public function setRegistry(DashboardBlockRegistry $registry)
    {
        $this->registry = $registry;

        return $this;
    }
}
