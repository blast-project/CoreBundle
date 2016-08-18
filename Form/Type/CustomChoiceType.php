<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;

class CustomChoiceType extends BaseAbstractType
{

    public function getParent()
    {
        return 'choice';
    }
    
    public function getBlockPrefix(){
        
        return 'librinfo_customchoice';
    }
}
