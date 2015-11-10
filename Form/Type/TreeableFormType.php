<?php

namespace Librinfo\CoreBundle\Form\Type;

use Librinfo\BaseEntitiesBundle\Entity\Repository\TreeableRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TreeableFormType extends AbstractType
{
//    public function configureOptions(OptionsResolver $resolver)
//    {
//        $placeholderDefault = function (Options $options)
//        {
//            if ($options['placeholder'])
//                return $options['required'] ? $options['placeholder'] . ' *' : $options['placeholder'];
//            else
//                return '';
//        };
//
//        $resolver->setDefaults([
//            'data_class'  => null,
//            'label'       => null,
//            'empty_label' => null,
//            'required'    => false,
//            'expanded'    => false,
//            'multiple'    => false,
//            'placeholder' => $placeholderDefault
//        ]);
//
//        $resolver->setAllowedTypes('expanded', array('null', 'bool'));
//        $resolver->setAllowedTypes('multiple', array('null', 'bool'));
//        $resolver->setAllowedTypes('required', array('null', 'bool'));
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function buildForm(FormBuilderInterface $builder, array $options)
//    {
//        if ($options['expanded'])
//        {
//            $builder->setDataMapper($options['multiple']
//                ? new CheckboxListMapper($options['choice_list'])
//                : new RadioListMapper($options['choice_list']));
//        }
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function buildView(FormView $view, FormInterface $form, array $options)
//    {
//
//    }

    public function buildView( FormView $view , FormInterface $form , array $options ) {

        $choices = [];

        foreach ( $view->vars['choices'] as $choice ) {
            $choices[] = $choice->data;
        }

        $choices = $this->buildTreeChoices( $choices );

        $view->vars['choices'] = $choices;

    }

    protected function buildTreeChoices( $choices , $level = 0 ) {

        $result = array();

        foreach ( $choices as $choice ){

            $result[] = new ChoiceView(
                str_repeat( '--' , $level ) . ' ' . $choice->getName(),
                $choice->getId(),
                $choice,
                []
            );

            if ( !$choice->getChildNodes()->isEmpty() )
                $result = array_merge(
                    $result,
                    $this->buildTreeChoices( $choice->getChildNodes() , $level + 1 )
                );

        }

        return $result;

    }

    public function configureOptions( OptionsResolver $resolver ) {
        $resolver->setDefaults([
            'query_builder' => function ( TreeableRepositoryInterface $repo ) {
                return $repo->getRootNodesQB();
            }
        ]);
    }

    public function getParent() {
        return 'entity';
    }

    public function getName()
    {
        return 'treeable';
    }
}
