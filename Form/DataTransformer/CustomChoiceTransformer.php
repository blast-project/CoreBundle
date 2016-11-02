<?php

namespace Librinfo\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomChoiceTransformer implements DataTransformerInterface
{
    private $repo;
    
    public function __construct($repo){
        $this->repo = $repo;
    }

    public function transform($choices)
    {
        if(null !== $choices)
        {
            $choice = $this->repo->find($choices);
            
            return $choice;
        }

        return $choices;
    }

    public function reverseTransform($choices)
    {
        return $choices;
    }
}