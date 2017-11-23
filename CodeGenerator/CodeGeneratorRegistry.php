<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\CodeGenerator;

class CodeGeneratorRegistry
{
    private static $generators = [];

    /**
     * Registers an entity code generator service.
     *
     * @param CodeGeneratorInterface $codeGenerator the entity code generator service
     *
     * @throws \Exception
     */
    public static function register(CodeGeneratorInterface $codeGenerator)
    {
        $class = new \ReflectionClass($codeGenerator);
        if (!$class->hasProperty('ENTITY_CLASS')) {
            throw new \Exception($class . ' must define a ENTITY_CLASS public property.');
        }
        if (!$class->hasProperty('ENTITY_FIELD')) {
            throw new \Exception($class . ' must define a ENTITY_FIELD public property.');
        }
        self::$generators[$codeGenerator::$ENTITY_CLASS][$codeGenerator::$ENTITY_FIELD] = $codeGenerator;
    }

    /**
     * Returns the last registered entity code generator service id for a given entity class and field.
     *
     * @param string $entityClass
     * @param string $entityField
     *
     * @return CodeGeneratorInterface
     *
     * @throws \Exception
     */
    public static function getCodeGenerator($entityClass, $entityField = 'code')
    {
        if (!isset(self::$generators[$entityClass][$entityField])) {
            throw new \Exception("There is no registered entity code generator for class $entityClass and field $entityField");
        }

        return self::$generators[$entityClass][$entityField];
    }

    /**
     * Returns registred code generators for specifyed entity class.
     *
     * @param string $entityClass
     *
     * @return array
     */
    public static function getCodeGenerators($entityClass = null)
    {
        if ($entityClass) {
            if (!isset(self::$generators[$entityClass])) {
                throw new \Exception("There is no registered entity code generator for class $entityClass");
            }

            return self::$generators[$entityClass];
        } else {
            return self::$generators;
        }
    }

    /**
     * @param string $entityClass
     *
     * @return bool
     */
    public static function hasGeneratorForClass($entityClass)
    {
        return !empty(self::$generators[$entityClass]);
    }
}
