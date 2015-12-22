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

abstract class CoreAdmin extends SonataAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        if ( !$this->formatUsingConfiguration($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        if ( !$this->formatUsingConfiguration($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        if ( !$this->formatUsingConfiguration($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        if ( !$this->formatUsingConfiguration($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    private function formatUsingConfiguration(BaseMapper $mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        
        $classes = ClassAnalyzer::getTraits($this->getClass());
        foreach ( array_reverse(array($this->getClass()) + class_parents($this->getClass())) as $class )
            $classes[] = $class;
        foreach ( array_reverse(array($this->getOriginalClass()) + $this->getParentClasses()) as $admin )
            $classes[] = $admin;
        
        $cpt = array('remove' => 0, 'add' => 0);
        foreach ( $classes as $class )
        if ( isset($librinfo[$class]) )
        {
            // copy stuff from elsewhere
            foreach ( array_reverse($list = array(get_class($mapper)) + class_parents($mapper)) as $mapper_class )
            if ( isset($librinfo[$class][$mapper_class]) )
            {
                if ( isset($librinfo[$class][$mapper_class]['_copy']) && $librinfo[$class][$mapper_class]['_copy'] )
                {
                    if ( !is_array($librinfo[$class][$mapper_class]['_copy']) )
                        $librinfo[$class][$mapper_class]['_copy'] = array($librinfo[$class][$mapper_class]['_copy']);
                    $list = $librinfo[$class][$mapper_class]['_copy'] + $list;
                }
            }

            // process data...
            foreach ( array_reverse($list) as $mapper_class )
            if ( isset($librinfo[$class][$mapper_class]) )
            {
                // remove fields
                if ( isset($librinfo[$class][$mapper_class]['remove']) )
                foreach ( $librinfo[$class][$mapper_class]['remove'] as $remove )
                if ( $mapper->has($remove) )
                {
                    $cpt['remove']++;
                    $mapper->remove($remove);
                }

                // add fields & more
                if ( isset($librinfo[$class][$mapper_class]['add']) )
                {
                    $cpt['add']++;
                    $this->addContent($mapper, $librinfo[$class][$mapper_class]['add']);
                }
            }
        }
        return array_sum($cpt);
    }

    private function addContent(BaseMapper $mapper, $group)
    {
        // flat organization
        if ( ! $mapper instanceof BaseGroupedMapper )
        {
            // options pre-treatment
            $options = array();
            if ( isset($group['_options']) )
            {
                $options = $group['_options'];
                unset($group['_options']);
            }
            
            // content
            foreach ( $group as $add => $opts )
                $this->addField($mapper, $add, $opts);
            
            // options
            if ( isset($options['fieldsOrder']) )
                $mapper->reorder($options['fieldsOrder']);
            
            return $mapper;
        }

        // if a grouped organization can be shapped
        // options
        $tabsOptions = null;
        if ( isset($group['_options']) )
        {
            $tabsOptions = $group['_options'];
            unset($group['_options']);
        }
        // content
        foreach ( $group as $tab => $tabcontent ) // loop on content...
        if ( self::arrayDepth($tabcontent) < 1 )
        {
            // direct add
            $this->addField($mapper, $tab, $tabcontent);
            $mapper->end()->end();
        }
        else
        {
            // groups/withs order
            $groupsOrder = null;
            if ( isset($tabcontent['_options']) && isset($tabcontent['_options']['groupsOrder']) )
            {
                $groupsOrder = $tabcontent['_options']['groupsOrder'];
                unset($tabcontent['_options']['groupsOrder']);
            }
            
            // tab
            if (!( isset($tabcontent['_options']['hideTitle']) && $tabcontent['_options']['hideTitle'] ))
                $mapper->tab($tab, isset($tabcontent['_options']) ? $tabcontent['_options'] : array());
            if ( isset($tabcontent['_options']) )
                unset($tabcontent['_options']);

            $finalOrder = null;

            // with
            if ( self::arrayDepth($tabcontent) > 0 )
            foreach ( $tabcontent as $with => $withcontent )
            {
                $opt = isset($withcontent['_options']) ? $withcontent['_options'] : array();
                $finalOrder = (isset($opt['fieldsOrder']) ? $opt['fieldsOrder'] : null);
                
                if (!( isset($opt['hideTitle']) && $opt['hideTitle'] ))
                    $mapper->with($with, $opt);
                if ( isset($withcontent['_options']) )
                    unset($withcontent['_options']);

                // final adds
                if ( self::arrayDepth($withcontent) > 0 )
                foreach ( $withcontent as $name => $options )
                {
                    $fieldDescriptionOptions = array();
                    if ( isset($options['_options']) )
                    {
                        $fieldDescriptionOptions = $options['_options'];
                        unset($options['_options']);
                    }
                    $this->addField($mapper, $name, $options, $fieldDescriptionOptions);
                }

                if ( $finalOrder != null )
                    $mapper->reorder($finalOrder);
                
                $mapper->end();
            }
            
            // order groups / withs (using tabs, because they are prioritary at the end)
            if ( isset($groupsOrder) )
            {
                $tabs = $mapper->getAdmin()->getFormTabs();
                $groups = $tabs[$tab]['groups'];
                $newgroups = array();
                foreach ( $groupsOrder as $groupname )
                if ( in_array("$tab.$groupname", $groups) )
                    $newgroups[] = "$tab.$groupname";
                foreach ( $groups as $groupname )
                if ( !in_array($groupname, $newgroups) )
                    $newgroups[] = $groupname;
                $tabs[$tab]['groups'] = $newgroups;
                $mapper->getAdmin()->setFormTabs($tabs);
            }

            $mapper->end();
        }
        
        // order tabs
        if ( isset($tabsOptions['tabsOrder']) )
        {
            $tabs = $mapper->getAdmin()->getFormTabs();
            $newtabs = array();
            foreach ( $tabsOptions['tabsOrder'] as $tabname )
            if ( isset($tabs[$tabname]) )
                $newtabs[$tabname] = $tabs[$tabname];
            foreach ( $tabs as $tabname => $tab )
            if ( !isset($newtabs[$tabname]) )
                $newtabs[$tabname] = $tab;
            $mapper->getAdmin()->setFormTabs($newtabs);
        }

        return $mapper;
    }

    private function addField(BaseMapper $mapper, $name, $options = array(), $fieldDescriptionOptions = array())
    {
        // avoid duplicates
        if ( $mapper->has($name) )
            $mapper->remove($name);

        if ( !is_array($options) )
            $options = array();

        $type = null;
        if ( isset($options['type']) )
        {
            $type = $options['type'];
            unset($options['type']);
        }
        $mapper->add($name, $type, $options, $fieldDescriptionOptions);
        return $mapper;
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
    protected function configureFields($function, BaseMapper $mapper, $class = NULL)
    {
        if ( !$class )
            $class = $this->getOriginalClass();
        return $class::$function($mapper);
    }
}

