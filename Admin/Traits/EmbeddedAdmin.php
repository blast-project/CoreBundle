<?php

namespace Blast\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Blast\CoreBundle\Admin\CoreAdmin;

trait EmbeddedAdmin
{
    use Base;
    
    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        CoreAdmin::configureFormFields($mapper);
        if ( $this->getParentFieldDescription() )
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
    }
    
    /**
     * @param FormMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        CoreAdmin::configureShowFields($mapper);
        if ( $this->getParentFieldDescription() )
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
    }
}

