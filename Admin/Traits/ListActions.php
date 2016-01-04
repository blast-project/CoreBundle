<?php

namespace Librinfo\CoreBundle\Admin\Traits;

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
     * @param  $action      array
     * @param  $name        string|null
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
        if (!( isset($action[$field]) && $action[$field] ))
            $action[$field] = $value;

        if ( !$action['action'] && !$action['route'] )
            return $this;

        $this->listActions[$name ? $name : $action['label']] = $action;
        return $this;
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
