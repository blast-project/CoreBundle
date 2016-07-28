<?php

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Librinfo\CoreBundle\Form\Type\EmptyChoiceList;
use Librinfo\CoreBundle\Form\DataTransformer\MultipleCheckboxesTransformer;

class CustomChoiceType extends BaseAbstractType
{

    public function getParent()
    {
        return 'choice';
    }
    
    public function getName(){
        
        return 'librinfo_customchoice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        //prevents validation errors on choice type dynamically added choices
//        $resolver->setDefaults(array(
//            'choice_list' => new EmptyChoiceList(),
//            'validation_groups' => false,
//        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //dump($builder);
//        if($options['multiple'] == true)
//            $builder->addModelTransformer(new MultipleCheckboxesTransformer)
//            ;
    }
}
