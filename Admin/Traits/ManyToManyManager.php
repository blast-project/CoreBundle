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
        $key = 'manyToMany'; // name of the key in the librinfo.yml

        // merge configuration/parameters
        foreach ( $this->getCurrentComposition() as $class )
        if ( isset($librinfo[$class])
          && isset($librinfo[$class]['manage'])
          && isset($librinfo[$class]['manage'][$key]) )
        {
            if ( !is_array($librinfo[$class]['manage'][$key]) )
                $librinfo[$class]['manage'][$key] = [$librinfo[$class]['manage'][$key]];
            $this->addManyToManyCollections($librinfo[$class]['manage'][$key]);
        }

        return $this;
    }
}
