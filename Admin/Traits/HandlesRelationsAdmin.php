<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Blast\Bundle\CoreBundle\Admin\CoreAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait HandlesRelationsAdmin
{
    use Base;

    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        CoreAdmin::configureFormFields($mapper);

        // relationships that will be handled by CollectionsManager
        $type = 'sonata_type_collection';

        foreach ($this->formFieldDescriptions as $fieldname => $fieldDescription) {
            if ($fieldDescription->getType() == $type) {
                $this->addManagedCollections($fieldname);
            }
        }

        // relationships that will be handled by ManyToManyManager
        foreach ($this->formFieldDescriptions as $fieldname => $fieldDescription) {
            $mapping = $fieldDescription->getAssociationMapping();
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && !$mapping['isOwningSide']) {
                $this->addManyToManyCollections($fieldname);
            }
        }

        if (method_exists($this, 'postConfigureFormFields')) {
            $this->postConfigureFormFields($mapper);
        }
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        CoreAdmin::configureShowFields($mapper);

        // relationships that will be handled by CollectionsManager
        $types = ['sonata_type_collection', 'orm_one_to_many'];
        foreach ($this->showFieldDescriptions as $fieldname => $fieldDescription) {
            if (in_array($fieldDescription->getType(), $types)) {
                $this->addManagedCollections($fieldname);
            }
        }

        // relationships that will be handled by ManyToManyManager
        foreach ($this->showFieldDescriptions as $fieldname => $fieldDescription) {
            $mapping = $fieldDescription->getAssociationMapping();
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && !$mapping['isOwningSide']) {
                $this->addManyToManyCollections($fieldname);
            }
        }

        if (method_exists($this, 'postConfigureShowFields')) {
            $this->postConfigureShowFields($mapper);
        }
    }
}
