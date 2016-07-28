<?php

namespace Librinfo\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MultipleCheckboxesTransformer implements DataTransformerInterface
{

    public function transform($choices)
    {
    dump($choices);
    dump(1);
        if (null === $choices) {
            return '';
        }

        return array('bonjour'=>'bonjour');
    }

    public function reverseTransform($choices)
    {
    dump($choices);
    dump(2);
        // no issue number? It's optional, so that's ok
        if (!$choices)
            return;

        return array('aurevoir'=>'aurevoir');
    }
}