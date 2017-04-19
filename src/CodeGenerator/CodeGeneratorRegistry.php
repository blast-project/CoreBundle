<?php

namespace Blast\CoreBundle\CodeGenerator;

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
        $class = get_class($codeGenerator);
        if (!defined("$class::ENTITY_CLASS"))
            throw new \Exception($class . ' must define a ENTITY_CLASS constant.');
        if (!defined("$class::ENTITY_FIELD"))
            throw new \Exception($class . ' must define a ENTITY_FIELD constant.');
        self::$generators[$codeGenerator::ENTITY_CLASS][$codeGenerator::ENTITY_FIELD] = $codeGenerator;
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

    /**
     * @param  string $entityClass
     * @return boolean
     */
    public static function hasGeneratorForClass($entityClass)
    {
        return !empty(self::$generators[$entityClass]);
    }

}