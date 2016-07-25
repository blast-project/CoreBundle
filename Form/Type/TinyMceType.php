<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TinyMceType extends BaseAbstractType
{

    public function getParent()
    {
        return 'textarea';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
