<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Doctrine\ORM;

use Blast\Bundle\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait OwningSideRelationHandlerTrait
{
    public function updateRelation($owning, $action = 'set')
    {
        $rc = new \ReflectionClass($this);
        $setter = $action . $rc->getShortName();

        $owning_rc = new \ReflectionClass($owning);
        if (ClassAnalyzer::hasMethod($owning_rc, $setter)) {
            $owning->$setter($this);
        }
    }
}
