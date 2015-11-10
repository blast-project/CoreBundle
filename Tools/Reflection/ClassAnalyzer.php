<?php

namespace Librinfo\CoreBundle\Tools\Reflection;

class ClassAnalyzer
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
     * @return boolean          TRUE or FALSE
     **/
    public static function hasTrait($class, $traitName)
    {
        return in_array($traitName, self::getTraits($class));
    }
    
    /**
     * hasProperty
     *
     * This static method says if a class has a property
     *
     * @param $class            A ReflectionClass object or a string describing an existing class
     * @param $propertyName     A string representing a property name
     * @return boolean
     **/
    public static function hasProperty($class, $propertyName)
    {
        $analyzer = new Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
        return $analyzer->hasProperty($class instanceof \ReflectionClass ? $class : new ReflectionClass($class), $propertyName);
    }
    
    /**
     * hasMethod
     *
     * This static method says if a class has a method
     *
     * @param $class            A ReflectionClass object or a string describing an existing class
     * @param $methodName       A string representing a method name
     * @return boolean
     **/
    public static function hasMethod($class, $methodName)
    {
        $rc = $class instanceof \ReflectionClass ? $class : new ReflectionClass($class);
        return $rc->hasMethod($methodName);
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
            $traits = self::_getTraits($trait, $traits); // first the embedded traits that come first...
            $traits[] = $trait->name;                    // then the current trait
        }

        return $traits;
    }
}
