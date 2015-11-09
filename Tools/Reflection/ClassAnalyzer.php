<?php

namespace Librinfo\CoreBundle\Tools\Reflection;

class ClassAnalyzer extends \Knp\DoctrineBehaviors\Reflection\ClassAnalyzer
{
    /**
     * getTraits
     *
     * This static method returns back all traits used by a given class
     * recursively
     *
     * @return array
     **/
    public static function getTraits($class, array $traits = null)
    {
        $rc = $class instanceof \ReflectionClass
            ? $class
            : new \ReflectionClass($class)
        ;
        if ( is_null($traits) )
            $traits = array();
        
        foreach ( $rc->getTraits() as $trait )
        {
            $traits[] = $trait->name;
            $traits = self::getTraits($trait, $traits);
        }

        return $traits;
    }
}
