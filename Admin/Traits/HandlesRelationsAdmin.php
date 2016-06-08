<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

trait HandlesRelationsAdmin
{

    use Base;

    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());

        // relationships that will be handled by CollectionsManager
        $type = 'sonata_type_collection';

        foreach ($this->formFieldDescriptions as $fieldname => $fieldDescription)
            if ($fieldDescription->getType() == $type)
                $this->addManagedCollections($fieldname);

        // relationships that will be handled by ManyToManyManager
        foreach ($this->formFieldDescriptions as $fieldname => $fieldDescription)
        {
            $mapping = $fieldDescription->getAssociationMapping();
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && !$mapping['isOwningSide'])
                $this->addManyToManyCollections($fieldname);
        }
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());

        // relationships that will be handled by CollectionsManager
        $types = ['sonata_type_collection', 'orm_one_to_many'];
        foreach ($this->showFieldDescriptions as $fieldname => $fieldDescription)
            if (in_array($fieldDescription->getType(), $types))
                $this->addManagedCollections($fieldname);

        // relationships that will be handled by ManyToManyManager
        foreach ($this->showFieldDescriptions as $fieldname => $fieldDescription)
        {
            $mapping = $fieldDescription->getAssociationMapping();
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && !$mapping['isOwningSide'])
                $this->addManyToManyCollections($fieldname);
        }
    }

}
