<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;

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
}

