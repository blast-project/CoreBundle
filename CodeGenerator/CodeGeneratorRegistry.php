<?php

namespace Librinfo\CoreBundle\CodeGenerator;

class CodeGeneratorRegistry
{
    private static $generators = [];

    /**
     * Registers an entity code generator service
     *
     * @param CodeGeneratorInterface $codeGenerator     the entity code generator service
     * @throws \Exception
     */
    public static function register(CodeGeneratorInterface $codeGenerator)
    {
        if (!isset($codeGenerator::$entityClass))
            throw new \Exception(get_class($codeGenerator) . ' has no public static entityClass property.');
        if (!isset($codeGenerator::$entityField))
            throw new \Exception(get_class($codeGenerator) . ' has no public static entityField property.');
        self::$generators[$codeGenerator::$entityClass][$codeGenerator::$entityField] = $codeGenerator;
    }

    /**
     * Returns the last registered entity code generator service id for a given entity class and field
     *
     * @param string $entityClass
     * @param string $entityField
     * @return CodeGeneratorInterface
     * @throws \Exception
     */
    public static function getCodeGenerator($entityClass, $entityField = 'code')
    {
        if (!isset(self::$generators[$entityClass][$entityField]))
            throw new \Exception("There is no registered entity code generator for class $entityClass and field $entityField");
        return self::$generators[$entityClass][$entityField];
    }

}