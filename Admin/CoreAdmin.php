<?php

namespace Librinfo\CoreBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Exception\InvalidParameterException;
use Symfony\Component\Validator\Mapping\Loader\YamlFileLoader;
use Sonata\AdminBundle\Admin\Admin as SonataAdmin;
use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;
use Librinfo\CoreBundle\Admin\Traits\CollectionsManager;
use Librinfo\CoreBundle\Admin\Traits\Mapper;
use Librinfo\CoreBundle\Admin\Traits\Templates;
use Librinfo\CoreBundle\Admin\Traits\PreEvents;
use Librinfo\CoreBundle\Admin\Traits\ManyToManyManager;
use Librinfo\CoreBundle\Admin\Traits\ListActions;
use Librinfo\CoreBundle\DataSource\Iterator;

abstract class CoreAdmin extends SonataAdmin
{
    use CollectionsManager,
        ManyToManyManager,
        Mapper,
        Templates,
        PreEvents,
        ListActions
    ;

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }
    
    /**
     * @param BaseMapper $mapper
     */
    protected function fixShowRoutes(BaseMapper $mapper)
    {
        foreach ( ['getShow', 'getList'] as $fct )
        foreach ( $this->$fct()->getElements() as $field )
        {
            $options = $field->getOptions();
            if ( $options['route']['name'] != 'edit' )
                continue;
            
            $options['route']['name'] = 'show';
            $field->setOptions($options);
        }
        
        return $this;
    }
    
    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        return new Iterator($datagrid->getQuery()->getQuery(), $this->getExportFields());
    }

    protected function getCurrentComposition()
    {
        // traits of the current Entity
        $classes = ClassAnalyzer::getTraits($this->getClass());
        // inheritance of the current Entity
        foreach ( array_reverse([$this->getClass()] + class_parents($this->getClass())) as $class )
            $classes[] = $class;
        // inheritance of the current Admin
        foreach ( array_reverse([$this->getOriginalClass()] + $this->getParentClasses()) as $admin )
            $classes[] = $admin;
        
        return $classes;
    }

    private function fallbackConfiguration(BaseMapper $mapper, $function)
    {
        // fallback
        $rm = new \ReflectionMethod($this->getParentClass(), $function);
        if ( $rm->class == $this->getParentClass() )
            $this->configureFields($function, $mapper, $this->getParentClass());
    }

    /**
     * Returns the level of depth of an array
     * @param  array  $array
     * @param  integer $level : do not use, just used for recursivity
     * @return int : depth
     */
    private static function arrayDepth( $array, $level = 0 )
    {
        if ( !$array )
            return $level;

        if ( !is_array($array) )
            return $level;

        $level++;
        foreach ( $array as $key => $value )
        if ( is_array($value) )
            $level = $level < self::arrayDepth($value, $level) ? self::arrayDepth($value, $level) : $level;

        return $level;
    }

    protected function getOriginalClass()
    {
        return get_called_class();
    }
    protected function getParentClasses()
    {
        return class_parents($this->getOriginalClass());
    }
    protected function getParentClass()
    {
        return get_parent_class($this->getOriginalClass());
    }
    protected function getGrandParentClass()
    {
        return get_parent_class(get_parent_class($this->getOriginalClass()));
    }
}

