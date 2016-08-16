<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;

class TinyMceType extends BaseAbstractType
{
    public function getParent()
    {
        return 'textarea';
    }
    
    public function getName(){
        
        return $this->getBlockPrefix();
    }
    
    public function getBlockPrefix()
    {
        return 'librinfo_tinymce';
    }
}
