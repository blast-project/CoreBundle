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

namespace Blast\CoreBundle\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Form\FormMapper;

class AdminCollector extends DataCollector
{
    /**
     * @var Collector
     */
    private $collector;

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [];

        $collectedData = $this->collector->getData();

        if ($collectedData instanceof FormMapper) {
            $entity = $collectedData->getAdmin()->getClass();
            $admin = $collectedData->getAdmin();

            $this->data['entity'] = [
                'display' => 'both', // 'toolbar', 'profiler', 'both'
                'class' => $entity,
                'file' => $this->getClassLink($entity),
            ];

            $this->data['admin'] = [
                'display' => 'both',
                'class' => get_class($admin),
                'file' => $this->getClassLink(get_class($admin)),
            ];

            $this->data['mapper'] = [
                'display' => 'both',
                'class' => get_class($collectedData),
                'file' => $this->getClassLink(get_class($collectedData)),
            ];

            $this->data['form tabs / groups'] = [
                'display' => 'toolbar',
                'class' => count($admin->getFormTabs()) . ' / ' . count($admin->getFormGroups()),
            ];

            $this->data['form'] = [
                'display' => 'profiler',
                'dump' => [
                    'tabs' => $admin->getFormGroups(),
                    'groups' => $admin->getFormTabs(),
                ],
            ];

            $this->data['show tabs / groups'] = [
                'display' => 'toolbar',
                'class' => count($admin->getShowTabs()) . ' / ' . count($admin->getShowGroups()),
            ];

            $this->data['show'] = [
                'display' => 'profiler',
                'dump' => [
                    'tabs' => $admin->getShowGroups(),
                    'groups' => $admin->getShowTabs(),
                ],
            ];
        } else {
            $this->data = [
                'entity' => ['display' => 'toolbar', 'class' => 'N/A', 'file' => false],
                'admin' => ['display' => 'toolbar', 'class' => 'N/A', 'file' => false],
                'mapper' => ['display' => 'toolbar', 'class' => 'N/A', 'file' => false],
            ];
        }
    }

    public function getData($name = null)
    {
        if ($name !== null) {
            if (array_key_exists($name, $this->data)) {
                return $this->data[$name];
            } else {
                return 'N/A';
            }
        } else {
            return $this->data;
        }
    }

    public function getName()
    {
        return 'blast.admin_collector';
    }

    /**
     * @return Collector
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * @param Collector collector
     *
     * @return self
     */
    public function setCollector(Collector $collector)
    {
        $this->collector = $collector;

        return $this;
    }

    private function getClassLink($class)
    {
        $reflector = new \ReflectionClass($class);

        return $reflector->getFileName();
    }
}
