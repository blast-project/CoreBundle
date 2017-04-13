<?php

namespace Blast\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait Templates
{
    /**
     * @param type $mapper
     *
     * @return type
     */
    protected function fixTemplates($mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        if (!isset($librinfo['configuration']) && isset($librinfo['configuration']['templates'])) {
            return $this;
        }

        $mapping = array(
            'ShowMapper' => 'show',
            'ListMapper' => 'list',
        );
        $rc = new \ReflectionClass($mapper);
        if (!isset($mapping[$rc->getShortName()])) {
            return $this;
        }

        $mapType = $mapping[$rc->getShortName()];
        if (!isset($librinfo['configuration']['templates'][$mapType])) {
            return $this;
        }

        // get back the new templates
        $templates = $librinfo['configuration']['templates'][$mapType];

        // checks if something has to be done
        foreach ($this->{$mapType.'FieldDescriptions'} as $fd) {
            if (isset($templates[$fd->getType()])) {
                $fd->setTemplate($templates[$fd->getType()]);
            }
        }

        return $this;
    }
}
