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

namespace Blast\Bundle\CoreBundle\CodeGenerator;

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
        $interface = 'Blast\Bundle\CoreBundle\CodeGenerator\CodeGeneratorInterface';
        if (!$rc->implementsInterface($interface)) {
            throw new \RuntimeException("Class $class should implement $interface");
        }
        $codeGenerator = new $class();
        $codeGenerator::setEntityManager($entityManager);

        return $codeGenerator;
    }
}
