<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;

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
