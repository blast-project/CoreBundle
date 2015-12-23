<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;

/**
 * This trait is used to delete many-to-many associated entities when the current
 * entity is not on the owning side
 */
trait ManyToManyManager
{

    protected $manyToManyCollections = [];

    /**
     * @return  array
     * */
    public function getManyToManyCollections()
    {
        return $this->manyToManyCollections;
    }

    /**
     * @param string|array $collections
     * @return self
     */
    public function addManyToManyCollections($collections)
    {
        if ( !is_array($collections) )
            $collections = [$collections];
        $this->manyToManyCollections = array_merge($this->manyToManyCollections, $collections);
        return $this;
    }

    /**
     * Delete many-to-many associated entities when the current entity is not on the owning side
     * This is called by the PreEvents::prePersistOrUpdate() method
     *
     * @param Object $object
     */
    protected function preUpdateManyToManyManager($object)
    {
        $this->configureManyToManyManager();

        $rc = new \ReflectionClass($this->getClass());
        $remove_method = 'remove' . ucfirst($rc->getShortName());

        foreach ( $this->manyToManyCollections as $fieldname ) {
            $get_method = 'get' . ucfirst($fieldname);
            $orig_collection = $object->$get_method()->getSnapshot();
            $new_collection = $object->$get_method();
            foreach ( $orig_collection as $entity )
                if ( !$new_collection->contains($entity) ) {
                    $entity->$remove_method($object);
                    $this->getModelManager()->update($entity);
                }
        }
    }

    private function configureManyToManyManager()
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        $key = 'managedManyToMany'; // name of the key in the librinfo.yml

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
            $this->addManyToManyCollections($librinfo[$class][$key]);
        }

        return $this;
    }
}
