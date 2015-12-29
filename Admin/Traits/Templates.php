<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

trait Templates
{
    /**
     */
    protected function fixTemplates($mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        if (! isset($librinfo['configuration']) && isset($librinfo['configuration']['templates']) )
            return $this;
        
        $mapping = [
            'ShowMapper' => 'show',
            'ListMapper' => 'list',
        ];
        $rc = new \ReflectionClass($mapper);
        if ( !isset($librinfo['configuration']['templates'][$mapping[$rc->getShortName()]]) )
            return $this;
        
        // get back the new templates
        $templates = $librinfo['configuration']['templates'][$mapping[$rc->getShortName()]];
        
        // checks if something has to be done
        foreach ( $this->{$mapping[$rc->getShortName()].'FieldDescriptions'} as $fd )
        if ( isset($templates[$fd->getType()]) )
            $fd->setTemplate($templates[$fd->getType()]);
        
        return $this;
    }
}

