<?php

namespace Librinfo\CoreBundle\Tools\Reflection;

use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class AdvancedClassAnalyzer extends ClassAnalyzer
{
    public function getTraits($reflectionClass, $asArray = false)
    {
        if (!$reflectionClass instanceof \ReflectionClass)
            $reflectionClass = new \ReflectionClass($reflectionClass);

        return $asArray ? array_keys($reflectionClass->getTraits()) : $reflectionClass->getTraits();
    }
}