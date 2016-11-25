<?php

namespace Blast\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait EmbeddedAdmin
{
    use Base;
    
    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
        if ( $this->getParentFieldDescription() )
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
    }
    
    /**
     * @param FormMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
        if ( $this->getParentFieldDescription() )
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
    }
}

