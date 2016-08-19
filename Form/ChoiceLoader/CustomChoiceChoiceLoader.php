<?php

namespace Librinfo\CoreBundle\Form\ChoiceLoader;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Doctrine\ORM\EntityManager;

class CustomChoiceChoiceLoader implements ChoiceLoaderInterface
{

    /** @var ChoiceListInterface */
    private $choiceList;

    /** @var EntityRepository */
    private $repository;

    /**
     *
     * @var EntityManager
     */
    private $manager;

    /**
     *
     * @var array
     */
    private $options;

    /**
     * @param EntityManager $manager
     * @param array $options
     */
    public function __construct(EntityManager $manager, $options)
    {
        $this->options = $options;
        $this->manager = $manager;
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        $values = array();
        foreach ($choices as $key => $choice)
        {
            if (is_callable($value))
            {
                $values[$key] = (string) call_user_func($value, $choice, $key);
            } else
            {
                $values[$key] = $choice;
            }
        }
        $this->choiceList = new ArrayChoiceList($values, $value);

        return $values;
    }

    public function loadChoiceList($value = null)
    {
        $class = $this->options['choices_class'];
        $field = $this->options['choices_field'];
        $repository = $this->manager->getRepository($this->options['choices_class']);

        if (isset($this->options['librinfo_choices']))
        {
            foreach ($this->options['librinfo_choices'] as $choice)
            {
                if ($repository->findBy(array('label' => $field, 'value' => $choice)) == null)
                {
                    $newChoice = new $class();
                    $newChoice->setLabel($field);
                    $newChoice->setValue($choice);

                    $this->manager->persist($newChoice);
                }
            }
            $this->manager->flush();
        }

        $choices = $repository->findBy(['label' => $field]);
        $choiceList = [];
        foreach ($choices as $choice)
            $choiceList[$choice->getValue()] = $choice->getValue();
        $this->choiceList = new ArrayChoiceList($choiceList, $value);

        return $this->choiceList;
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        $choices = array();
        foreach ($values as $key => $val)
        {
            if (is_callable($value))
            {
                $choices[$key] = (string) call_user_func($value, $val, $key);
            } else
            {
                $choices[$key] = $val;
            }
        }
        $this->choiceList = new ArrayChoiceList($values, $value);

        return $choices;
    }

}
