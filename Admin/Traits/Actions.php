<?php

namespace Blast\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

trait Actions
{
    protected function addActions($mapper)
    {
        $actionKey = '_actions';
        $mapperClass = ListMapper::class;
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ( $this->getCurrentComposition() as $class )
        if ( isset($blast[$class][$mapperClass]) )
        {
            $config = $blast[$class][$mapperClass];
            
            if( isset($blast['all'][$mapperClass]) )
                $config = array_merge_recursive(
                    $config, 
                    $blast['all'][$mapperClass]
                );
            
            
            if ( isset($config['add'][$actionKey]) )
            {
                $listFields = $this->getListFieldDescriptions();

                if ( isset($listFields['_action']) )
                {
                    $conf = $listFields['_action'];
                    $options = $conf->getOptions();
                    $actions = $options['actions'];
    
                    foreach ( $config['add'][$actionKey] as $key => $action )
                        $actions[$key] = $action;
    
                    $options['actions'] = $actions;
                    $conf->setOptions($options);
                    $listFields['_action'] = $conf;
                }
            }
        }
    }
    
    protected function removeActions(RouteCollection $collection)
    {
        //Removing list actions by disabling the corresponding route
        $actionKey = '_actions';
        $mapperClass = ListMapper::class;
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach( $this->getCurrentComposition() as $class )
        if( isset($blast[$class][$mapperClass]) )
        {
            $config = $blast[$class][$mapperClass];
            
            if( isset($blast['all'][$mapperClass]) )
                $config = array_merge_recursive(
                    $config, 
                    $blast['all'][$mapperClass]
                );
            
            if( isset($config['remove'][$actionKey]) )
                foreach($config['remove'][$actionKey] as $key)
                    if( $collection->has($key) )
                        $collection->remove($key);
        }
    }
}
