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

namespace Blast\CoreBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

class BlastCoreBundleTest extends KernelTestCase
{
    protected $container;

    protected function setUp()
    {
        static::bootKernel();

        /* @var Container $container */
        $this->container = self::$kernel->getContainer();
    }

    public function testServicesAreInitializable()
    {
        $serviceIds = array_filter($this->container->getServiceIds(), function ($serviceId) {
            return 0 === strpos($serviceId, 'blast_core.');
        });

        foreach ($serviceIds as $serviceId) {
            $this->assertNotNull($this->container->get($serviceId));
        }

        $this->assertContains('blast_core.form.type.entity_code', $serviceIds);
    }

    public function testServicesExists()
    {
        /*
         * @todo is it usefull to test if service exist or not
         */

        $this->assertContains('blast_core.code_generator_factory', $this->container->getServiceIds());

        $this->assertContains('blast_core.code_generators', $this->container->getServiceIds());
        $this->assertContains('blast_core.form.type.entity_code', $this->container->getServiceIds());
        #$this->assertContains('blast_core.label.strategy.librinfo', $this->container->getServiceIds());
    }
}
