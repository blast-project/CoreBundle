<?php

namespace Librinfo\CoreBundle\Form\Type;

class TreeableChoiceFormType extends TreeableFormType
{

    public function getParent()
    {
        return 'treeable';
    }

    public function getName()
    {
        return 'treeable_choice';
    }
}