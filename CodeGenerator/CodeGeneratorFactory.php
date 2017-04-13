<?php

namespace Blast\CoreBundle\CodeGenerator;

use Doctrine\ORM\EntityManager;

class CodeGeneratorFactory
{
    /**
     * Creates an entity code generator service.
     *
     * @param string        $class
     * @param EntityManager $entityManager
     *
     * @return CodeGeneratorInterface
     */
    public static function create($class, EntityManager $entityManager)
    {
        $rc = new \ReflectionClass($class);
        $interface = 'Blast\CoreBundle\CodeGenerator\CodeGeneratorInterface';
        if (!$rc->implementsInterface($interface)) {
            throw new \RuntimeException("Class $class should implement $interface");
        }
        $codeGenerator = new $class();
        $codeGenerator::setEntityManager($entityManager);

        return $codeGenerator;
    }
}
