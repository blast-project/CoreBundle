<?php

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Librinfo\CoreBundle\Form\DataTransformer\CheckboxTransformer;

class CustomCheckboxType extends BaseAbstractType
{

    public function getParent()
    {
        return 'checkbox';
    }

    public function getName()
    {

        return 'librinfo_customcheckbox';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->addModelTransformer(new CheckboxTransformer());
    }
}
