<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait CollectionsManager
{
    protected $managedCollections = [];
    
    public function getManagedCollections()
    {
        return $this->managedCollections;
    }
    
    public function prePersist($object)
    {
        $this->preUpdateOrPersist($object);
    }

    public function preUpdate($object)
    {
        $this->preUpdateOrPersist($object);
    }
    
    protected function preUpdateOrPersist($object)
    {
        // global configuration
        $this->configureCollectionsManager();
        
        // for each given collection
        foreach ( $this->managedCollections as $coll )
        {
            // preparing stuff
            $target = $this->getModelManager()
                ->getEntityManager($object)
                ->getClassMetadata($this->getClass())
                ->associationMappings[$coll]['targetEntity']
            ;
            $rctarget = new \ReflectionClass($target);
            $rcentity = new \ReflectionClass($this->getClass());
            $method = 'get'.ucfirst($coll);
            
            // delete
            foreach ( $object->$method()->getSnapshot() as $subobj )
            if ( !$object->$method()->contains($subobj) )
                $this->getModelManager()->delete($subobj);
            
            // insert/update (forcing the foreign key to be set to $this->getId(), for instance)
            foreach ( $object->$method() as $subobj )
                $subobj->{'set'.ucfirst($rcentity->getShortName())}($object);
        }
        
        return $this;
    }

    private function configureCollectionsManager()
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        $key = 'managedCollections'; // name of the key in the librinfo.yml
        
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
    
    /**
     * function addManagedCollections
     *
     * @param $collections      array or string, describing the collections to manage
     * @return CoreAdmin        $this
     **/
    public function addManagedCollections($collections)
    {
        if ( !is_array($collections) )
            $collections = array($collections);
        $this->managedCollections += $collections;
        
        return $this;
    }
}
