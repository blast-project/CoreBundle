<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait Mapper
{
    private function configureMapper(BaseMapper $mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        
        // traits of the current Entity
        $classes = ClassAnalyzer::getTraits($this->getClass());
        // inheritance of the current Entity
        foreach ( array_reverse(array($this->getClass()) + class_parents($this->getClass())) as $class )
            $classes[] = $class;
        // inheritance of the current Admin
        foreach ( array_reverse(array($this->getOriginalClass()) + $this->getParentClasses()) as $admin )
            $classes[] = $admin;
        
        // builds the configuration, based on the Mapper class
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
        
        // removing empty groups in tabs definition
        if ( $mapper instanceof BaseGroupedMapper )
        {
            $fcts = [
                'tabs' => $this->getFormTabs() !== false
                    ? ['getter' => 'getFormTabs', 'setter' => 'setFormTabs']
                    : ['getter' => 'getShowTabs', 'setter' => 'setShowTabs'],
                'groups' => $this->getFormGroups() !== false
                    ? ['getter' => 'getFormGroups', 'setter' => 'setFormGroups']
                    : ['getter' => 'getShowGroups', 'setter' => 'setShowGroups'],
            ];
            foreach ( $tabs = $this->{$fcts['tabs']['getter']}() as $tabkey => $tab )
            foreach ( $tab['groups'] as $groupkey => $group )
            if ( !isset($this->{$fcts['groups']['getter']}()[$group]) )
                unset($tabs[$tabkey]['groups'][$groupkey]);
            $this->{$fcts['tabs']['setter']}($tabs);
        }
        
        //return array_sum($cpt);
        return $this;
    }

    private function addContent(BaseMapper $mapper, $group)
    {
        // flat organization (DatagridMapper / ListMapper...)
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
                $groups = $tabs[$tab]['groups'] ? $tabs[$tab]['groups'] : array();
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
        if ( isset($tabsOptions['tabsOrder']) && $tabs = $mapper->getAdmin()->getFormTabs() )
        {
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
        
        // save-and-remove CoreBundle-specific options
        $extras = array();
        foreach ( array(
            'template' => 'setTemplate',
            'initializeAssociationAdmin' => NULL,
        ) as $extra => $method )
        if ( isset($fieldDescriptionOptions[$extra]) )
        {
            $extras[$extra] = array($method, $fieldDescriptionOptions[$extra]);
            unset($fieldDescriptionOptions[$extra]);
        }
        
        $mapper->add($name, $type, $options, $fieldDescriptionOptions);
        
        // apply extra options
        foreach ( $extras as $extra => $call )
        {
            if ( $call[0] )
               	$mapper->get($name)->{$call[0]}($call[1]);
            else switch ( $extra ) {
            case 'initializeAssociationAdmin':
                // only if "true"
                if ( !$call[1] )
                    break;
                
                // initialize the association-admin
                $mapper->get($name)->getAssociationAdmin()->configureShowFields(new ShowMapper(
                    $mapper->get($name)->getAssociationAdmin()->getShowBuilder(),
                    $mapper->get($name)->getAssociationAdmin()->getShow(),
                    $mapper->get($name)->getAssociationAdmin()
                ));
                
                // set the efficient template
                if ( !isset($extras['template']) )
                    $mapper->get($name)->setTemplate('LibrinfoCoreBundle:CRUD:show_association_admin.html.twig');
                break;
            }
        }
        
        return $mapper;
    }
    
    protected function configureFields($function, BaseMapper $mapper, $class = NULL)
    {
        if ( !$class )
            $class = $this->getOriginalClass();
        return $class::$function($mapper);
    }
}

