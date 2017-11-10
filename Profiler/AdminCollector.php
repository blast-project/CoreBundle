<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Profiler;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;

class AdminCollector extends DataCollector
{
    const TYPE_NOT_MANAGED = 'Not Managed';

    /**
     * @var Collector
     */
    private $collector;

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            DataCollection::DESTINATION_TOOLBAR  => [],
            DataCollection::DESTINATION_PROFILER => [],
        ];

        $collectedData = $this->collector->getData();

        $hooks = 0;

        foreach ($collectedData as $k => $dataCollection) {
            $data = $dataCollection->getData();

            if (preg_replace('/\#[0-9]*\W/', '', $k) === 'Managed classes') {
                $this->addToProfiler($k, 'Managed classes', [
                    'display'         => DataCollection::DESTINATION_TOOLBAR, // 'toolbar', 'profiler', 'both'
                    'class'           => count($data),
                ]);
            }

            if (preg_replace('/^\#[0-9]*\W/', '', $k) === 'Hook') {
                ++$hooks;
            }

            if ($data instanceof BaseGroupedMapper || $data instanceof BaseMapper) {
                $entity = $data->getAdmin()->getClass();
                $admin = $data->getAdmin();

                $this->addToProfiler($k, 'entity', [
                    'display' => DataCollection::DESTINATION_PROFILER,
                    'class'   => $entity,
                    'file'    => $this->getClassLink($entity),
                ]);

                $this->addToProfiler($k, 'admin', [
                    'display' => DataCollection::DESTINATION_PROFILER,
                    'class'   => get_class($admin),
                    'file'    => $this->getClassLink(get_class($admin)),
                ]);

                // Not really usefull because other type of mapper have not been tested
                //
                // $this->addToProfiler($k, 'mapper', [
                //     'display' => DataCollection::DESTINATION_PROFILER,
                //     'class'   => get_class($data),
                //     'file'    => $this->getClassLink(get_class($data)),
                // ]);

                $this->addToProfiler($k, 'form tabs / groups', [
                    'display' => DataCollection::DESTINATION_PROFILER,
                    'class'   => count($admin->getFormTabs()) . ' / ' . count($admin->getFormGroups()),
                ]);

                $this->addToProfiler($k, 'form', [
                    'display'         => DataCollection::DESTINATION_PROFILER,
                    'Tabs and Groups' => [
                        'tabs'   => $admin->getFormTabs(),
                        'groups' => $admin->getFormGroups(),
                    ],
                ]);

                $this->addToProfiler($k, 'show tabs / groups', [
                    'display' => DataCollection::DESTINATION_PROFILER,
                    'class'   => count($admin->getShowTabs()) . ' / ' . count($admin->getShowGroups()),
                ]);

                $this->addToProfiler($k, 'show', [
                    'display'         => DataCollection::DESTINATION_PROFILER,
                    'Tabs and Groups' => [
                        'tabs'   => $admin->getShowTabs(),
                        'groups' => $admin->getShowGroups(),
                    ],
                ]);
            } else {
                $this->addToProfiler($k, $dataCollection->getName(), $dataCollection);
            }
        }

        $this->addToProfiler('Hook registered', 'Hooks', [
            'display'         => DataCollection::DESTINATION_TOOLBAR, // 'toolbar', 'profiler', 'both'
            'class'           => $hooks,
        ]);
    }

    public function getData($name = null)
    {
        if ($name === null) {
            return $this->data;
        }

        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return self::TYPE_NOT_MANAGED;
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

    private function addToProfiler($rootKey, $key, $data)
    {
        if ($data instanceof DataCollection) {
            $this->data[$data->getDestination()][$rootKey][$key] = $data->getData();
        } else {
            switch ($data['display']) {
                case DataCollection::DESTINATION_TOOLBAR:
                case DataCollection::DESTINATION_PROFILER:
                    $this->data[$data['display']][$rootKey][$key] = $data;
                    break;
                case DataCollection::DESTINATION_BOTH:
                    $this->data[DataCollection::DESTINATION_TOOLBAR][$rootKey][$key] = $data;
                    $this->data[DataCollection::DESTINATION_PROFILER][$rootKey][$key] = $data;
                    break;
                default:
                    $this->data[DataCollection::DESTINATION_PROFILER][$rootKey][$key] = $data;
            }
        }
    }
}
