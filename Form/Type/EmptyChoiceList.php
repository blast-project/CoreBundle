<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Librinfo\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

/**
 * Description of EmptyChoiceList
 *
 * @author romain
 */
class EmptyChoiceList implements ChoiceListInterface
{
    public function getChoices()
    {
        return array();
    }

    public function getChoicesForValues(array $values)
    {
        return $values;
    }

    public function getIndicesForChoices(array $choices)
    {
        return $choices;
    }

    public function getIndicesForValues(array $values)
    {
        return $values;
    }

    public function getPreferredViews()
    {
        return array();
    }

    public function getRemainingViews()
    {
        return array();
    }

    public function getValues()
    {
        return array();
    }

    public function getValuesForChoices(array $choices)
    {
        return $choices;
    }
}
