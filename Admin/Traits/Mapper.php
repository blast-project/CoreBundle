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

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper
                ? ['getter' => 'getShowTabs', 'setter' => 'setShowTabs']
                : ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper
                ? ['getter' => 'getShowGroups', 'setter' => 'setShowGroups']
                : ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];
        
        $classes = $this->getCurrentComposition();
        
        // builds the configuration, based on the Mapper class
        $cpt = ['remove' => 0, 'add' => 0];
        foreach ( $classes as $class )
        if ( isset($librinfo[$class]) )
        {
            // copy stuff from elsewhere
            foreach ( array_reverse($list = array_merge([get_class($mapper)], array_values(class_parents($mapper)))) as $mapper_class )
            if ( isset($librinfo[$class][$mapper_class]) )
            {
                if ( isset($librinfo[$class][$mapper_class]['_copy']) && $librinfo[$class][$mapper_class]['_copy'] )
                {
                    if ( !is_array($librinfo[$class][$mapper_class]['_copy']) )
                        $librinfo[$class][$mapper_class]['_copy'] = [$librinfo[$class][$mapper_class]['_copy']];
                    foreach ( $librinfo[$class][$mapper_class]['_copy'] as $copy )
                        $list = array_merge(
                            $list,
                            array_merge([$copy], array_values(class_parents($copy)))
                        );
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
                    
                    // compensating the partial removal in Sonata Admin, that does not touch the groups when removing a field
                    if ( $mapper instanceof BaseGroupedMapper )
                    foreach ( $groups = $this->{$fcts['groups']['getter']}() as $groupkey => $group )
                    if ( isset($group['fields'][$remove]) )
                    {
                        unset($groups[$groupkey]['fields'][$remove]);
                        if ( !$groups[$groupkey]['fields'] )
                            unset($groups[$groupkey]);
                        $this->{$fcts['groups']['setter']}($groups);
                    }
                }

                // add fields & more
                if ( isset($librinfo[$class][$mapper_class]['add']) )
                {
                    $cpt['add']++;
                    $this->addContent($mapper, $librinfo[$class][$mapper_class]['add']);
                }
            }
        }
        
        if ( $mapper instanceof BaseGroupedMapper )
        {
            // removing empty groups
            foreach ( $groups = $this->{$fcts['groups']['getter']}() as $groupkey => $group )
            if ( !$group['fields'] )
                unset($groups[$groupkey]);
            $this->{$fcts['groups']['setter']}($groups);
            
            // removing empty tabs
            foreach ( $tabs = $this->{$fcts['tabs']['getter']}() as $tabkey => $tab )
            {
                foreach ( $tab['groups'] as $groupkey => $group )
                if ( !isset($this->{$fcts['groups']['getter']}()[$group]) )
                    unset($tabs[$tabkey]['groups'][$groupkey]);
                if ( !$tabs[$tabkey]['groups'] )
                    unset($tabs[$tabkey]);
            }
            $this->{$fcts['tabs']['setter']}($tabs);
        }
        
        //return array_sum($cpt);
        $this->fixTemplates($mapper);
        dump($this);
        return $this;
    }

    private function addContent(BaseMapper $mapper, $group)
    {
        // flat organization (DatagridMapper / ListMapper...)
        if ( ! $mapper instanceof BaseGroupedMapper )
        {
            // options pre-treatment
            $options = [];
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

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper
                ? ['getter' => 'getShowTabs', 'setter' => 'setShowTabs']
                : ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper
                ? ['getter' => 'getShowGroups', 'setter' => 'setShowGroups']
                : ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];
        
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
            
            $endgroup = $endtab = false;
            
            // tab
            if ( isset($tabcontent['_options']['hideTitle']) && $tabcontent['_options']['hideTitle']
              || $mapper instanceof ShowMapper )
            {
                $tabs = $this->{$fcts['tabs']['getter']}();
                $groups = $this->{$fcts['groups']['getter']}();
                if ( isset($tabs[$tab]) )
                {
                    $tabs[$tab]['auto_created'] = true;
                    $this->{$fcts['tabs']['setter']}($tabs);
                    
                    foreach ( $groups as $groupkey => $group )
                    if ( !isset($groups[$group['name']]) )
                    {
                        $groups[$group['name']] = $group;
                        unset($groups[$groupkey]);
                    }
                    $this->{$fcts['groups']['setter']}($groups);
                }
            }
            else
            {
                $mapper->tab($tab, isset($tabcontent['_options']) ? $tabcontent['_options'] : []);
                $endtab = true;
            }
            
            if ( isset($tabcontent['_options']) )
                unset($tabcontent['_options']);

            $finalOrder = null;

            // with
            if ( self::arrayDepth($tabcontent) > 0 )
            foreach ( $tabcontent as $with => $withcontent )
            {
                $opt = isset($withcontent['_options']) ? $withcontent['_options'] : [];
                $finalOrder = (isset($opt['fieldsOrder']) ? $opt['fieldsOrder'] : null);
                
                if (!( isset($opt['hideTitle']) && $opt['hideTitle'] ))
                {
                    $endtab = true;
                    $endgroup = true;
                    $mapper->with($with, $opt);
                }
                if ( isset($withcontent['_options']) )
                    unset($withcontent['_options']);

                // final adds
                if ( self::arrayDepth($withcontent) > 0 )
                foreach ( $withcontent as $name => $options )
                {
                    $fieldDescriptionOptions = [];
                    if ( isset($options['_options']) )
                    {
                        $fieldDescriptionOptions = $options['_options'];
                        unset($options['_options']);
                    }
                    $this->addField($mapper, $name, $options, $fieldDescriptionOptions);
                    $endgroup = $endtab = true;
                }

                if ( $finalOrder != null )
                    $mapper->reorder($finalOrder);
                
                if ( $endgroup )
                    $mapper->end();
            }
            
            // order groups / withs (using tabs, because they are prioritary at the end)
            if ( isset($groupsOrder) )
            {
                // preparing
                $otabs = $mapper->getAdmin()->{$fcts['tabs']['getter']}();
                $groups = $mapper->getAdmin()->{$fcts['groups']['getter']}();
                
                // pre-ordering
                $newgroups = [];
                foreach ( $groupsOrder as $groupname )
                {
                    $buf = isset($otabs[$tab]) && $otabs[$tab]['auto_created'] ? "" : "$tab.";
                    if ( isset($otabs[$tab]) && in_array("$buf$groupname", $otabs[$tab]['groups']) )
                        $newgroups[] = "$buf$groupname";
                }
                
                // ordering tabs
                foreach ( isset($otabs[$tab]['groups']) && $otabs[$tab]['groups']
                    ? $otabs[$tab]['groups']
                    : []
                as $groupname )
                {
                    if ( !in_array($groupname, $newgroups) )
                        $newgroups[] = $groupname;
                }
                $otabs[$tab]['groups'] = $newgroups;
                
                // "persisting"
                $mapper->getAdmin()->{$fcts['tabs']['setter']}($otabs);
            }

            if ( $endtab )
                $mapper->end();
        }
        
        // ordering tabs
        if ( isset($tabsOptions['tabsOrder']) && $tabs = $this->{$fcts['tabs']['getter']}() )
        {
            $newtabs = [];
            foreach ( $tabsOptions['tabsOrder'] as $tabname )
            if ( isset($tabs[$tabname]) )
                $newtabs[$tabname] = $tabs[$tabname];
            foreach ( $tabs as $tabname => $tab )
            if ( !isset($newtabs[$tabname]) )
                $newtabs[$tabname] = $tab;
            $this->{$fcts['tabs']['setter']}($newtabs);
        }
        
        // ordering the ShowMapper
        if ( $mapper instanceof ShowMapper )
        {
            $order = [];
            $groups = $this->{$fcts['groups']['getter']}();
            foreach ( $this->{$fcts['tabs']['getter']}() as $tab )
            foreach ( $tab['groups'] as $group )
            if ( isset($groups[$group]) )
                $order[] = $group;
            foreach ( $groups as $name => $content )
            if ( !in_array($name, $order) )
                $order[] = $name;
            
            $newgroups = [];
            foreach ( $order as $grp )
                $newgroups[$grp] = $groups[$grp];
            $this->{$fcts['groups']['setter']}($newgroups);
        }

        return $mapper;
    }

    private function addField(BaseMapper $mapper, $name, $options = [], $fieldDescriptionOptions = [])
    {
        // avoid duplicates
        if ( $mapper->has($name) )
            $mapper->remove($name);

        if ( !is_array($options) )
            $options = [];

        $type = null;
        if ( isset($options['type']) )
        {
            $type = $options['type'];
            unset($options['type']);
        }
        
        // save-and-remove CoreBundle-specific options
        $extras = [];
        foreach ( [
            'template' => 'setTemplate',
            'initializeAssociationAdmin' => NULL,
        ] as $extra => $method )
        if ( isset($fieldDescriptionOptions[$extra]) )
        {
            $extras[$extra] = [$method, $fieldDescriptionOptions[$extra]];
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

