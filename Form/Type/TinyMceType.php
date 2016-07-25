<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;

class TinyMceType extends BaseAbstractType
{
    public function getParent()
    {
        return 'textarea';
    }
}
