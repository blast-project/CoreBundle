<?php

namespace Blast\CoreBundle\Tools\Reflection;

class ClassAnalyzer
{
    /**
     * Returns all parents of a class (parent, parent of parent, parent of parent's parent and so on)
     *
     * @param ReflectionClass|string   $class   A ReflectionClass object or a class name
     * @return array
     */
    public static function getAncestors($class)
    {
        $rc = $class instanceof \ReflectionClass ? \ReflectionClass($class->getName()) : new \ReflectionClass($class);
        $ancestors  = [];
        while ($parent = $rc->getParentClass()) {
            $ancestors[] = $parent->getName();
            $rc = $parent;
        }
        return $ancestors;
    }

    /**
     * getTraits
     *
     * This static method returns back all traits used by a given class
     * recursively
     *
     * @param ReflectionClass|string   $class   A ReflectionClass object or a class name
     * @return array
     */
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
     * @param ReflectionClass|string   $class            A ReflectionClass object or a class name
     * @param string                   $traitName        A string representing an existing trait
     * @return boolean
     */
    public static function hasTrait($class, $traitName)
    {
        return in_array($traitName, self::getTraits($class));
    }

    /**
     * hasProperty
     *
     * This static method says if a class has a property
     *
     * @param ReflectionClass|string   $class            A ReflectionClass object or a class name
     * @param string                   $propertyName      A string representing an existing property
     * @return boolean
     */
    public static function hasProperty($class, $propertyName)
    {
        $rc = $class instanceof \ReflectionClass ? $class : new ReflectionClass($class);

        if ($rc->hasProperty($propertyName)) {
            return true;
        }

        $parentClass = $rc->getParentClass();

        if (false === $parentClass) {
            return false;
        }

        return $this->hasProperty($parentClass, $propertyName);
    }

    /**
     * hasMethod
     *
     * This static method says if a class has a method
     *
     * @param ReflectionClass|string  $class            A ReflectionClass object or a class name
     * @param string                  $methodName       a method name
     * @return boolean
     */
    public static function hasMethod($class, $methodName)
    {
        $rc = $class instanceof \ReflectionClass ? $class : new ReflectionClass($class);
        return $rc->hasMethod($methodName);
    }

    /**
     * @param ReflectionClass|string   $class         A ReflectionClass object or a class name
     * @param array                    $traits        An array of traits (strings)
     * @return array
     */
    private static function _getTraits($class, array $traits = null)
    {
        $rc = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
        if ( is_null($traits) )
            $traits = array();

        // traits being embedded through the current class or the embedded traits
        foreach ( $rc->getTraits() as $trait )
        {
            $traits = self::_getTraits($trait, $traits); // first the embedded traits that come first...
            if ( !in_array($trait->name, $traits) )
                $traits[] = $trait->name;                    // then the current trait
        }

        // traits embedded by the parent class
        if ( $rc->getParentClass() )
            $traits = self::_getTraits($rc->getParentClass(), $traits);

        return $traits;
    }

}
