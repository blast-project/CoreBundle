<?php

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Doctrine\ORM\EntityManager;
use Librinfo\CoreBundle\Form\AbstractType as BaseAbstractType;
use Librinfo\CoreBundle\Form\ChoiceLoader\CustomChoiceChoiceLoader;

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
        $choiceLoader = function (Options $options) use ($manager) {
            $repository = $manager->getRepository($options['choices_class']);
            $field = $options['choices_field'];
            return new CustomChoiceChoiceLoader($repository, $field);
        };

        $resolver->setDefaults([
            'placeholder' => '',
            'choice_loader' => $choiceLoader
        ]);

        $resolver->setRequired(['choices_class', 'choices_field']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices_class'] = $options['choices_class'];
        $view->vars['choices_field'] = $options['choices_field'];
    }
}
