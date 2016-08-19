<?php

namespace Librinfo\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class MultipleChoiceTransformer implements DataTransformerInterface
{
    public function transform($choices)
    {
        return explode('||', $choices);
    }

    public function reverseTransform($choices)
    {
        return implode('||', $choices);
    }
}