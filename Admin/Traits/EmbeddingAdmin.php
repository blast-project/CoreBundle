<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;

trait EmbeddingAdmin
{
    use Base;
    
    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
        
        $type = 'sonata_type_collection';
        foreach ( $this->formFieldDescriptions as $fieldname => $fieldDescription )
        if ( $fieldDescription->getType() == $type )
            $this->addManagedCollections($fieldname);
    }
}
