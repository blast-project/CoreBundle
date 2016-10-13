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
     * @return string
     */
    public static function generate($entity);

    /**
     * @param string $code
     * @param mixed  $entity
     * @return boolean
     */
    public static function validate($code, $entity = null);

    /**
     * @return string
     */
    public static function getHelp();
}

