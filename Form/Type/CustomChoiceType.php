<?php

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Librinfo\CoreBundle\Form\ChoiceLoader\CustomChoiceChoiceLoader;
use Librinfo\CoreBundle\Form\DataTransformer\MultipleChoiceTransformer;

class CustomChoiceType extends BaseAbstractType
{

    /** @var EntityManager */
    private $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function getParent()
    {
        return 'choice';
    }
    
    public function getBlockPrefix(){
        
        return 'librinfo_customchoice';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $manager = $this->manager;
        $defaultClass = '\Librinfo\CoreBundle\Entity\SelectChoice';

        $choiceLoader = function (Options $options) use ($manager) {
            return new CustomChoiceChoiceLoader($manager, $options);
        };

        $resolver->setDefaults([
            'placeholder'   => '',
            'choices_class' => $defaultClass,
            'choice_loader' => $choiceLoader,
            'is_filter'     => false
        ]);
        $resolver->setRequired(['choices_class', 'choices_field']);
        $resolver->setDefined('librinfo_choices');
        $resolver->setDefined('is_filter');
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices_class'] = $options['choices_class'];
        $view->vars['choices_field'] = $options['choices_field'];
        $view->vars['is_filter'] = $options['is_filter'];
    }

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        if ($options['multiple'] == true)
            $builder->addModelTransformer(new MultipleChoiceTransformer());
    }

}
