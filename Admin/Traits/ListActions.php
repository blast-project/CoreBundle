<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Datagrid\ListMapper;

trait ListActions
{
    /**
     * @var array
     **/
    protected $listActions = [];
    private $_listActionLoaded = false;

    /**
     * getListActions().
     *
     * @return array
     **/
    public function getListActions()
    {
        if (!$this->_listActionLoaded) {
            $this->handleListActions($this->listActions);
        }

        return $this->listActions;
    }

    /**
     * setListActions().
     *
     * @param  $actions     array
     *
     * @return self
     **/
    public function setListActions(array $actions)
    {
        foreach ($actions as $key => $action) {
            $this->addListAction($key, $action);
        }

        return $this;
    }

    /**
     * addListAction().
     *
     * @param  $name        string|null
     * @param  $action      array
     *
     * @return self
     **/
    public function addListAction($name, array $action)
    {
        foreach ([
            'label' => $name,
            'params' => [],
            'translation_domain' => '',
            'action' => '',
            'route' => '',
        ] as $field => $value) {
            if (empty($action[$field])) {
                $action[$field] = $value;
            }
        }

        if (!$action['action'] && !$action['route']) {
            return $this;
        }

        $this->listActions[$name ? $name : $action['label']] = $action;

        return $this;
    }

    /**
     * Add routes for custom list actions
     * overrides SonataAdmin/Admin::configureRoutes() so that it is called automatically by Admin::buildRoutes().
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class][ListMapper::class]['add']['_actions'])) {
                $actions = $blast[$class][ListMapper::class]['add']['_actions'] ? $blast[$class][ListMapper::class]['add']['_actions'] : [];

                foreach ($actions['actions'] as $key => $action) {
                    if (!empty($action['route'])) {
                        $routeSuffix = $action['route'];
                    } else {
                        $routeSuffix = $key;
                    }
                    $collection->add($key, $this->getRouterIdParameter() . '/' . $routeSuffix);
                }
            }
        }
    }

    /**
     * removeListAction().
     *
     * @param  $name        string|null
     *
     * @return self
     **/
    public function removeListAction($name)
    {
        unset($this->listActions[$name]);

        return $this;
    }

    /**
     * hasListAction().
     *
     * @param  $name        string|null
     *
     * @return self
     **/
    public function hasListAction($name)
    {
        return isset($this->listActions[$name]);
    }
}
