<?php

namespace Librinfo\CoreBundle\Form\ChoiceLoader;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Doctrine\ORM\EntityRepository;

class CustomChoiceChoiceLoader implements ChoiceLoaderInterface
{
    /** @var ChoiceListInterface */
    private $choiceList;
    /** @var EntityRepository */
    private $repository;
    /** @var string */
    private $field;

    /**
     * @param EntityRepository $repository
     * @param string $field
     */
    public function __construct (EntityRepository $repository, $field) {
        $this->repository = $repository;
        $this->field = $field;
    }


    public function loadValuesForChoices(array $choices, $value = null)
    {
        $values = array();
        foreach ($choices as $key => $choice) {
            if (is_callable($value)) {
                $values[$key] = (string)call_user_func($value, $choice, $key);
            }
            else {
                $values[$key] = $choice;
            }
        }
        $this->choiceList = new ArrayChoiceList($values, $value);

        return $values;
    }

    public function loadChoiceList($value = null)
    {
        $choices = $this->repository->findBy(['label' => $this->field]);
        $choiceList = [];
        foreach($choices as $choice)
            $choiceList[$choice->getValue()] = $choice->getValue();
        $this->choiceList = new ArrayChoiceList($choiceList, $value);

        return $this->choiceList;
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        $choices = array();
        foreach ($values as $key => $val) {
            if (is_callable($value)) {
                $choices[$key] = (string)call_user_func($value, $val, $key);
            }
            else {
                $choices[$key] = $val;
            }
        }
        $this->choiceList = new ArrayChoiceList($values, $value);

        return $choices;
    }
}