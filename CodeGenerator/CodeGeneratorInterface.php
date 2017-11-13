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

namespace Blast\CoreBundle\CodeGenerator;

use Doctrine\ORM\EntityManager;

interface CodeGeneratorInterface
{
    /**
     * @param EntityManager $entityManager
     */
    public static function setEntityManager(EntityManager $entityManager);

    /**
     * @param mixed $entity
     *
     * @return string
     */
    public static function generate($entity);

    /**
     * @param string $code
     * @param mixed  $entity
     *
     * @return bool
     */
    public static function validate($code, $entity = null);

    /**
     * @return string
     */
    public static function getHelp();
}
