<?php

namespace Librinfo\CoreBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Librinfo\BaseEntitiesBundle\Entity\Repository\TreeableRepositoryInterface;
use Librinfo\BaseEntitiesBundle\EventListener\Traits\ClassChecker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TreeableFormType extends AbstractType
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $choices = [];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event)
        {
            $form = $event->getForm();
            $repo = $this->em->getRepository($form->getConfig()->getAttribute('data_collector/passed_options')['class']);

            if ($repo instanceof TreeableRepositoryInterface)
            {
                $this->choices = $this->buildTreeChoices($repo->getRootNodesWithTree());

                $form->getConfig()->getType()->getOptionsResolver()->setDefaults([
                    'choices' => $this->choices
                ]);
            }
        });

        parent::buildForm($builder, $options);

    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = [];

        foreach ($view->vars['choices'] as $choice)
            $choices[] = $choice->data;

        $choices = $this->choices;

        $view->vars['choices'] = $choices;
    }

    protected function buildTreeChoices($choices, $level = 0)
    {
        $result = array();

        foreach ($choices as $choice)
        {
            $result[] = new ChoiceView(
                $choice,
                $choice->getId(),
                $this->generateTreeSybols($choice->getName(), $level),
                []
            );

            if ($choice->getChildNodes()->count() != 0)
                $result = array_merge(
                    $result,
                    $this->buildTreeChoices($choice->getChildNodes(), $level + 1)
                );
        }

        return $result;
    }

    protected function generateTreeSybols($label, $level)
    {
        $spaces = '└─ ';
        for ($i = 0; $i < $level; $i++)
            $spaces = '⠀' . $spaces;

        return $spaces . $label;
    }

    public function getParent()
    {
        return 'entity';
    }

    public function getName()
    {
        return 'treeable';
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}
