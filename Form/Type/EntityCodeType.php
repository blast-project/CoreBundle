<?php

namespace Blast\CoreBundle\Form\Type;

use Blast\CoreBundle\Form\AbstractType as BaseAbstractType;

class EntityCodeType extends BaseAbstractType
{
    public function getParent() {
        return 'text';
    }

    public function getBlockPrefix()
    {
        return 'librinfo_entitycode';
    }
    
}
