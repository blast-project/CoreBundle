<?php

namespace Blast\CoreBundle\Admin\Traits;

use Doctrine\ORM\Mapping\ClassMetadataInfo;


trait CollectionsManager
{

    protected $managedCollections = [];

    /**
     * function getManagedCollections
     *
     * @return  array
     * */
    public function getManagedCollections()
    {
        return $this->managedCollections;
    }

    /**
     * function addManagedCollections
     *
     * @param $collections      array or string, describing the collections to manage
     * @return CoreAdmin        $this
     * */
    public function addManagedCollections($collections)
    {
        if (!is_array($collections))
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
        foreach ($this->managedCollections as $coll)
        if ( isset($this->formFieldDescriptions[$coll]) )
        {
            if ($coll == 'packagings') dump($this->formFieldDescriptions[$coll]);
            // preparing stuff
            if ( $admin_code = $this->formFieldDescriptions[$coll]->getOption('admin_code') ) {
                $targetAdmin = $this->getConfigurationPool()->getAdminByAdminCode($admin_code);
            }
            else {
                $target = $this->getModelManager()
                    ->getEntityManager($object)
                    ->getClassMetadata($this->getClass())
                    ->associationMappings[$coll]['targetEntity']
                ;

                $rctarget = new \ReflectionClass($target);
                $targetAdmin = $this->getConfigurationPool()->getAdminByClass($rctarget->getName());
            }

            $rcentity = new \ReflectionClass($this->getClass());
            $method = 'get' . ucfirst($coll);

            $subObjects = $object->$method();

            // insert/update (forcing the foreign key to be set to $this->getId(), for instance)
            if( $subObjects != null )
                foreach ($subObjects as $subobj)
                {
                    if ($this->formFieldDescriptions[$coll]->getMappingType() != ClassMetadataInfo::MANY_TO_MANY)
                        $subobj->{'set' . ucfirst($rcentity->getShortName())}($object);

                    if( $targetAdmin != null )
                        $targetAdmin->prePersist($subobj);
                }

            if (!$subObjects instanceof Doctrine\ORM\PersitentCollection || $subObjects->count() == 0)
                continue;

            // delete
            foreach ($subObjects->getSnapshot() as $subobj)
                if (!$subObjects->contains($subobj))
                    $this->getModelManager()->delete($subobj);
        }

        return $this;
    }

    private function configureCollectionsManager()
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        $key = 'collections'; // name of the key in the librinfo.yml
        // merge configuration/parameters
        foreach ($this->getCurrentComposition() as $class)
            if (isset($librinfo[$class]) && isset($librinfo[$class]['manage']) && isset($librinfo[$class]['manage'][$key]))
            {
                if (!is_array($librinfo[$class]['manage'][$key]))
                    $librinfo[$class]['manage'][$key] = [$librinfo[$class]['manage'][$key]];
                $this->addManagedCollections($librinfo[$class]['manage'][$key]);
            }

        return $this;
    }

}
