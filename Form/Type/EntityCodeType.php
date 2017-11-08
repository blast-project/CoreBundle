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

namespace Blast\Bundle\CoreBundle\Form\Type;

use Blast\Bundle\CoreBundle\Form\AbstractType as BaseAbstractType;

class EntityCodeType extends BaseAbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getBlockPrefix()
    {
        return 'blast_entitycode';
    }
}
