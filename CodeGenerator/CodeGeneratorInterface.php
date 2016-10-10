<?php

namespace Librinfo\CoreBundle\CodeGenerator;

use Doctrine\ORM\EntityManager;

interface CodeGeneratorInterface
{
    /**
     * @param EntityManager $entityManager
     */
    public static function setEntityManager(EntityManager $entityManager);

    /**
     * @param mixed $entity
     */
    public static function generate($entity);

    /**
     * @param string $code
     * @param mixed  $entity
     */
    public static function validate($code, $entity = null);
}

