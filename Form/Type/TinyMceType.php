<?php

namespace Blast\CoreBundle\Form\Type;

use Blast\CoreBundle\Form\AbstractType as BaseAbstractType;

class TinyMceType extends BaseAbstractType
{
    public function getParent()
    {
        return 'textarea';
    }
    
    public function getBlockPrefix()
    {
        return 'blast_tinymce';
    }
}
