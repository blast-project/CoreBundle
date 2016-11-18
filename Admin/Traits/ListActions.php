<?php

namespace Librinfo\CoreBundle\Admin\Traits;

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
     * getListActions()
     *
     * @return array
     **/
    public function getListActions()
    {
        if ( !$this->_listActionLoaded )
            $this->addPresetListActions($this->listActions);
        return $this->listActions;
    }

    /**
     * setListActions()
     *
     * @param  $actions     array
     * @return $this
     **/
    public function setListActions(array $actions)
    {
        foreach ( $actions as $key => $action )
            $this->addListAction($key, $action);
        return $this;
    }

    /**
     * addListAction()
     *
     * @param  $name        string|null
     * @param  $action      array
     * @return $this
     **/
    public function addListAction($name, array $action)
    {
        foreach ( [
            'label' => $name,
            'params' => [],
            'translation_domain' => '',
            'action' => '',
            'route' => '',
        ] as $field => $value )
        if ( empty($action[$field]) )
            $action[$field] = $value;

        if ( !$action['action'] && !$action['route'] )
            return $this;

        $this->listActions[$name ? $name : $action['label']] = $action;
        return $this;
    }

    /**
     * Add routes for custom list actions
     * overrides SonataAdmin/Admin::configureRoutes() so that it is called automatically by Admin::buildRoutes()
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');

        foreach ($this->getCurrentComposition() as $class)
        if (isset($librinfo[$class][ListMapper::class]['add']['_actions']))
        {
            $actions = $librinfo[$class][ListMapper::class]['add']['_actions'] ? $librinfo[$class][ListMapper::class]['add']['_actions'] : [];

            foreach ($actions['actions'] as $key => $action)
            {
                if (!empty($action['route']))
                    $routeSuffix = $action['route'];
                else
                    $routeSuffix = $key;
                $collection->add($key, $this->getRouterIdParameter().'/'.$routeSuffix);
            }
        }
    }

    /**
     * removeListAction()
     *
     * @param  $name        string|null
     * @return $this
     **/
    public function removeListAction($name)
    {
        unset($this->listActions[$name]);
        return $this;
    }

    /**
     * hasListAction()
     *
     * @param  $name        string|null
     * @return $this
     **/
    public function hasListAction($name)
    {
        return isset($this->listActions[$name]);
    }
}
