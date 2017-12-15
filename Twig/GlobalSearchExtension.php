<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalSearchExtension extends \Twig_Extension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getGlobalSearchAdmins', array($this, 'parseConfig')),
        );
    }

    public function parseConfig()
    {
        $blast = $this->container->getParameter('blast');
        $admins = [];

        if (isset($blast['configuration']['search'])) {
            $search = $blast['configuration']['search'];

            if (isset($search['add'])) {
                foreach ($search['add'] as $key => $admin) {
                    $admins[] = $admin;
                }
            }

            if (isset($search['remove'])) {
                foreach ($search['remove'] as $admin) {
                    if (in_array($admin, $admins)) {
                        unset($admins[$admin]);
                    }
                }
            }
        }

        return $admins;
    }

    public function getName()
    {
        return 'blast_global_search_extension';
    }
}
