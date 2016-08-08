<?php

namespace Librinfo\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CheckboxTransformer implements DataTransformerInterface
{

    public function transform($choice)
    {
        if($choice == 1)
            return true;
    
        return false;
    }

    public function reverseTransform($choice)
    {
        if($choice == true)
            return 1;
        
        return 0;
    }
}