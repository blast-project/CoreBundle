<?php

namespace Librinfo\CoreBundle\CodeGenerator;

use Doctrine\ORM\EntityManager;

class CodeGeneratorFactory
{
    /**
     * @param string $class
     * @param EntityManager $entityManager
     * @return mixed
     */
    public static function create($class, EntityManager $entityManager)
    {
        $codeGenerator = new $class();
        $codeGenerator::setEntityManager($entityManager);
        return $codeGenerator;
    }

}