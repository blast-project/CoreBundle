<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

trait ManyToManyManager
{

    public function prePersistManyToManyManager($object)
    {
        $this->preUpdateOrPersistManyToManyManager($object);
    }

    public function preUpdateManyToManyManager($object)
    {
        $this->preUpdateOrPersistManyToManyManager($object);
    }

    /**
     * Delete many-to-many associated entities when the current entity is not on the owning side
     *
     * @param Object $object
     */
    protected function preUpdateOrPersistManyToManyManager($object)
    {
        $rc = new \ReflectionClass($this->getClass());
        $remove_method = 'remove'.ucfirst($rc->getShortName());

        foreach ( $this->formFieldDescriptions as $fieldname => $fieldDescription )
        {
            $mapping = $fieldDescription->getAssociationMapping();
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && !$mapping['isOwningSide']) {
                $get_method = 'get'.ucfirst($fieldname);
                $orig_collection = $object->$get_method()->getSnapshot();
                $new_collection = $object->$get_method();
                foreach ($orig_collection as $entity)
                if ( ! $new_collection->contains($entity) ) {
                    $entity->$remove_method($object);
                    $this->getModelManager()->update($entity);
                }
            }
        }
    }


    private function configureManyToManyManager()
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');

        // traits of the current Entity
        $classes = ClassAnalyzer::getTraits($this->getClass());
        // inheritance of the current Entity
        foreach ( array_reverse(array($this->getClass()) + class_parents($this->getClass())) as $class )
            $classes[] = $class;
        // inheritance of the current Admin
        foreach ( array_reverse(array($this->getOriginalClass()) + $this->getParentClasses()) as $admin )
            $classes[] = $admin;

        // merge configuration/parameters
        foreach ( $classes as $class )
        if ( isset($librinfo[$class])
          && isset($librinfo[$class][$key]) )
        {
            if ( !is_array($librinfo[$class][$key]) )
                $librinfo[$class][$key] = [$librinfo[$class][$key]];
            $this->addManagedCollections($librinfo[$class][$key]);
        }

        return $this;
    }

}
