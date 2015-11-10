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
     * @param $class            A ReflectionClass object or a string describing an existing class
     * @return array
     **/
    public static function getTraits($class)
    {
        return self::_getTraits($class);
    }
    
    /**
     * hasTraits
     *
     * This static method returns back all traits used by a given class
     * recursively
     *
     * @param $class            A ReflectionClass object or a string describing an existing class
     * @param $traitName        A string representing an existing trait
     * @param $isRecursive      boolean (useless, compatibility regards)
     * @return boolean          TRUE or FALSE
     **/
    public function hasTrait($class, $traitName, $isRecursive = true)
    {
        return in_array($traitName, self::getTraits($class));
    }


    private static function _getTraits($class, array $traits = null)
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
            $traits = self::_getTraits($trait, $traits);
        }

        return $traits;
    }
}
