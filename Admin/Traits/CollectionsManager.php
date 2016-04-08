<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait CollectionsManager
{
    protected $managedCollections = [];

    /**
     * function getManagedCollections
     *
     * @return  array
     **/
    public function getManagedCollections()
    {
        return $this->managedCollections;
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
       $this->managedCollections = array_merge($this->managedCollections, $collections);

        return $this;
    }

    public function prePersistCollectionsManager($object)
    {
        $this->preUpdateOrPersistCollectionsManager($object);
    }

    public function preUpdateCollectionsManager($object)
    {
        $this->preUpdateOrPersistCollectionsManager($object);
    }

    protected function preUpdateOrPersistCollectionsManager($object)
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

            dump($object->$method());

            // delete
            if ( $object->$method()->count() > 0 && $object->$method() instanceof Doctrine\ORM\PersitentCollection )

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
        $key = 'collections'; // name of the key in the librinfo.yml

        // merge configuration/parameters
        foreach ( $this->getCurrentComposition() as $class )
        if ( isset($librinfo[$class])
          && isset($librinfo[$class]['manage'])
          && isset($librinfo[$class]['manage'][$key]) )
        {
            if ( !is_array($librinfo[$class]['manage'][$key]) )
                $librinfo[$class]['manage'][$key] = [$librinfo[$class]['manage'][$key]];
            $this->addManagedCollections($librinfo[$class]['manage'][$key]);
        }

        return $this;
    }
}
