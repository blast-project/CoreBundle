<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

/**
 * This trait is used to delete many-to-many associated entities when the current
 * entity is not on the owning side.
 */
trait ManyToManyManager
{
    protected $manyToManyCollections = [];

    /**
     * @return array
     * */
    public function getManyToManyCollections()
    {
        return $this->manyToManyCollections;
    }

    /**
     * @param string|array $collections
     *
     * @return self
     */
    public function addManyToManyCollections($collections)
    {
        if (!is_array($collections)) {
            $collections = [$collections];
        }
        $this->manyToManyCollections = array_merge($this->manyToManyCollections, $collections);

        return $this;
    }

    /**
     * Delete many-to-many associated entities when the current entity is not on the owning side
     * This is called by the PreEvents::prePersistOrUpdate() method.
     *
     * @param object $object
     */
    protected function preUpdateManyToManyManager($object)
    {
        $this->configureManyToManyManager();

        $rc = new \ReflectionClass($this->getClass());
        $remove_method = 'remove' . ucfirst($rc->getShortName());

        foreach ($this->manyToManyCollections as $fieldname) {
            $get_method = 'get' . ucfirst($fieldname);
            $orig_collection = $object->$get_method()->getSnapshot();
            $new_collection = $object->$get_method();
            foreach ($orig_collection as $entity) {
                if (!$new_collection->contains($entity)) {
                    $entity->$remove_method($object);
                    $this->getModelManager()->update($entity);
                }
            }
        }
    }

    private function configureManyToManyManager()
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        $key = 'manyToMany'; // name of the key in the blast.yml

        // merge configuration/parameters
        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class])
          && isset($blast[$class]['manage'])
          && isset($blast[$class]['manage'][$key])) {
                if (!is_array($blast[$class]['manage'][$key])) {
                    $blast[$class]['manage'][$key] = [$blast[$class]['manage'][$key]];
                }
                $this->addManyToManyCollections($blast[$class]['manage'][$key]);
            }
        }

        return $this;
    }
}
