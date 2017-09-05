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

namespace Blast\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FakeHook extends \Twig_Extension
{
    private $hookAlreadyRegistred = null;

    public function __construct(ContainerInterface $container)
    {
        $this->hookAlreadyRegistred = $container->get('twig')->hasExtension('blast_hook');
    }

    public function getFunctions()
    {
        $functions = [];
        if ($this->hookAlreadyRegistred === false) {
            $functions[] = new \Twig_SimpleFunction('blast_hook', array($this, 'displayFakeHook'), ['is_safe' => ['html']]);
        }

        return $functions;
    }

    public function displayFakeHook()
    {
        return '<!-- Blast hook -->';
    }
}
