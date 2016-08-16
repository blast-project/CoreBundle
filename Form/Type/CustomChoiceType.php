<?php

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Librinfo\CoreBundle\Form\DataTransformer\CustomChoiceTransformer;

class CustomChoiceType extends BaseAbstractType
{
    private $repo;
    
    public function __construct($manager)
    {
        $this->repo = $manager->getRepository('LibrinfoVarietiesBundle:SelectChoice');
    }
    
    public function getParent()
    {
        return 'entity';
    }
    
    public function getBlockPrefix(){
        
        return 'librinfo_customchoice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $label = 'professional_sowing_period'; 
        
        $choices = $this->repo->findBy(array(
            'label' => $label
            )        
        );
        $choiceList = array();
        
        foreach($choices as $choice)
        {
            $choiceList[$choice->getValue] = $choice;
        }
        
        $resolver->setDefaults(array(
            'class'       => 'LibrinfoVarietiesBundle:SelectChoice',
            'choices'     => $choiceList,
            'placeholder' => ''
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CustomChoiceTransformer($this->repo));
    }
}
