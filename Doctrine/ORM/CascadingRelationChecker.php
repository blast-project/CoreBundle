<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CascadingRelationChecker
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $undeleteReasons;

    /**
     * Hande entity relations before trying to remove it.
     *
     * @param mixed $entity a Doctrine entity object
     * @param array $idx    list of entity ids to be removed
     *
     * @return
     */
    public function beforeEntityDelete($entity, &$idx)
    {
        $this->undeleteReasons = [];

        $entityMetadata = $this->em->getClassMetadata(get_class($entity));

        $associations = $entityMetadata->getAssociationMappings();

        foreach ($associations as $association) {
            if (in_array('remove', $association['cascade'])) {
                continue; // Skip of cascading enabled
            }

            if (!in_array($association['type'], [ClassMetadata::ONE_TO_MANY])) {
                continue; // Handling only _TO_MANY relations
            }

            $propertyAccessor = new PropertyAccessor();
            $associationData = $propertyAccessor->getValue($entity, $association['fieldName']);

            if ($associationData instanceof Collection) {
                if ($associationData->count() > 0) {
                    $this->removeFromIdsArray($entityMetadata->getIdentifierValues($entity), $idx, $association);
                }
            } elseif ($associationData !== null) {
                $this->removeFromIdsArray($entityMetadata->getIdentifierValues($entity), $idx, $association);
            }
        }

        return $this->undeleteReasons;
    }

    protected function removeFromIdsArray($id, &$idx, $association)
    {
        $this->undeleteReasons[] = $association['fieldName'];

        foreach ($id as $k => $entityId) {
            foreach ($idx as $l => $idxId) {
                if ($idxId === $entityId) {
                    unset($idx[$l]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em): void
    {
        $this->em = $em;
    }
}
