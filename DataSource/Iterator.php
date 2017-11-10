<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\DataSource;

use Exporter\Source\DoctrineORMQuerySourceIterator;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class Iterator extends DoctrineORMQuerySourceIterator
{
    /**
     * {@inheritdoc}
     * Allows to get back embedded arrays, in 2 dimensions max.
     * e.g.: fields positions.name & positions.organism.name will be returned as :
     *       [
     *          'name' => 'myEntity',
     *          'positions' => [
     *              ['name' => 'XXX', 'organism.name' => 'YYY'],
     *              ['name' => 'AAA', 'organism.name' => 'BBB'],
     *          ]
     *       ];.
     */
    public function current()
    {
        $data = [];
        $propertyPaths = $this->propertyPaths;

        // then complete it for "fields" representing a collection of sub-entities
        foreach ($this->propertyPaths as $i => $propertyPath) {
            try {
                if (($property = $this->propertyAccessor->getValue($this->iterator->current()[0], $propertyPath)) instanceof PersistentCollection) {
                    if (!(isset($data[(string) $propertyPath]) && is_array($data[(string) $propertyPath]))) {
                        $data[(string) $propertyPath] = [];
                    }

                    foreach ($property as $subEntity) {
                        $data[(string) $propertyPath][] = (string) $subEntity;
                    }

                    unset($this->propertyPaths[$i]);
                }
            } catch (NoSuchPropertyException $e) {
                $collection = preg_replace('/\..+$/', '', $propertyPath);
                $subProperty = preg_replace('/^.+\./U', '', $propertyPath);
                if ($collection != (string) $propertyPath && $subProperty) {
                    if (($property = $this->propertyAccessor->getValue($this->iterator->current()[0], $collection)) instanceof PersistentCollection) {
                        if (!(isset($data[$collection]) && is_array($data[$collection]))) {
                            $data[$collection] = [];
                        }

                        foreach ($property as $subEntity) {
                            $data[$collection][spl_object_hash($subEntity)][$subProperty] = (string) $this->propertyAccessor->getValue($subEntity, $subProperty);
                        }
                    }
                }
                unset($this->propertyPaths[$i]);
            }
        }

        // first do the "normal" stuff
        $data = array_merge($data, parent::current());

        $this->propertyPaths = $propertyPaths;

        return $data;
    }

    /**
     * getQuery().
     *
     * @return Doctrine\ORM\Query
     **/
    public function getQuery()
    {
        return $this->query;
    }
}
