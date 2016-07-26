<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Admin\FieldDescriptionCollection;
use Librinfo\CoreBundle\Tools\Reflection\ClassAnalyzer;

trait Mapper
{
    /**
     * Force tabulations on Show views
     * @var boolean
     */
    protected $forceTabs = false;

    protected function configureMapper(BaseMapper $mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        $classes = $this->getCurrentComposition();
        $this->getConfigurationPool()->getContainer()->get('logger')
                ->debug('[LibrinfoCoreBundle] Processing the configuration in this order: ' . implode(', ', $classes));

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper ? ['getter' => 'getShowTabs', 'setter' => 'setShowTabs'] : ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper ? ['getter' => 'getShowGroups', 'setter' => 'setShowGroups'] : ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];

        // Figure out if we have to display tabs on the Show view
        $this->forceTabs = false;
        if ( $mapper instanceof ShowMapper )
        foreach( $classes as $class )
        if ( isset($librinfo[$class]) )
        foreach ( array_reverse($list = array_merge([get_class($mapper)], array_values(class_parents($mapper)))) as $mapper_class )
        if ( !empty($librinfo[$class][$mapper_class]['forceTabs']) )
            $this->forceTabs = true;

        // builds the configuration, based on the Mapper class
        $cpt = ['remove' => 0, 'add' => 0];
        foreach ($classes as $class)
            if (isset($librinfo[$class]))
            {
                // copy stuff from elsewhere
                foreach (array_reverse($list = array_merge([get_class($mapper)], array_values(class_parents($mapper)))) as $mapper_class)
                    if (isset($librinfo[$class][$mapper_class]))
                    {
                        if (isset($librinfo[$class][$mapper_class]['_copy']) && $librinfo[$class][$mapper_class]['_copy'])
                        {
                            if (!is_array($librinfo[$class][$mapper_class]['_copy']))
                                $librinfo[$class][$mapper_class]['_copy'] = [$librinfo[$class][$mapper_class]['_copy']];
                            foreach ($librinfo[$class][$mapper_class]['_copy'] as $copy)
                                $list = array_merge(
                                        $list, array_merge([$copy], array_values(class_parents($copy)))
                                );
                        }
                    }

                // process data...
                foreach (array_reverse($list) as $mapper_class)
                    if (isset($librinfo[$class][$mapper_class]))
                    {
                        // do not parse _batch_actions & co
                        foreach (['_list_action', '_batch_action', '_export_format', '_extra_templates'] as $specialKey)
                        {
                            if (isset($librinfo[$class][$mapper_class]['remove']) && ($key = array_search($specialKey, $librinfo[$class][$mapper_class]['remove'])) !== false)
                                unset($librinfo[$class][$mapper_class]['remove'][$key]);
                            if (isset($librinfo[$class][$mapper_class]['add']) && isset($librinfo[$class][$mapper_class]['add'][$specialKey]))
                                unset($librinfo[$class][$mapper_class]['add'][$specialKey]);
                        }

                        // remove fields
                        if (isset($librinfo[$class][$mapper_class]['remove']))
                            foreach ($librinfo[$class][$mapper_class]['remove'] as $remove)
                                if ($mapper->has($remove))
                                {
                                    $cpt['remove'] ++;
                                    $mapper->remove($remove);

                                    // compensating the partial removal in Sonata Admin, that does not touch the groups when removing a field
                                    if ($mapper instanceof BaseGroupedMapper)
                                        foreach ($groups = $this->{$fcts['groups']['getter']}() as $groupkey => $group)
                                            if (isset($group['fields'][$remove]))
                                            {
                                                unset($groups[$groupkey]['fields'][$remove]);
                                                if (!$groups[$groupkey]['fields'])
                                                    unset($groups[$groupkey]);
                                                $this->{$fcts['groups']['setter']}($groups);
                                            }
                                }

                        // add fields & more
                        if (isset($librinfo[$class][$mapper_class]['add']))
                        {
                            $cpt['add'] ++;
                            $this->addContent($mapper, $librinfo[$class][$mapper_class]['add']);
                        }
                    }
            }

        if ($mapper instanceof BaseGroupedMapper)
        {
            // removing empty groups
            $groups = $this->{$fcts['groups']['getter']}();
            if (is_array($groups))
            {
                foreach ($groups as $groupkey => $group)
                    if (!$group['fields'])
                        unset($groups[$groupkey]);
                $this->{$fcts['groups']['setter']}($groups);
            }

            // removing empty tabs
            $tabs = $this->{$fcts['tabs']['getter']}();
            if (is_array($tabs))
            {
                foreach ($tabs as $tabkey => $tab)
                {
                    foreach ($tab['groups'] as $groupkey => $group)
                        if (!isset($this->{$fcts['groups']['getter']}()[$group]))
                            unset($tabs[$tabkey]['groups'][$groupkey]);
                    if (!$tabs[$tabkey]['groups'])
                        unset($tabs[$tabkey]);
                }
                $this->{$fcts['tabs']['setter']}($tabs);
            }
        }
        //return array_sum($cpt);
        $this->fixTemplates($mapper);
        if (!$mapper instanceof FormMapper)
            $this->fixShowRoutes($mapper);
        return $this;
    }

    protected function addContent(BaseMapper $mapper, $group)
    {

        // flat organization (DatagridMapper / ListMapper...)
        if (!$mapper instanceof BaseGroupedMapper)
        {
            // options pre-treatment
            $options = [];
            if (isset($group['_options']))
            {
                $options = $group['_options'];
                unset($group['_options']);
            }

            // content
            foreach ($group as $add => $opts)
                $this->addField($mapper, $add, $opts);

            // options
            if (isset($options['fieldsOrder']))
                $mapper->reorder($options['fieldsOrder']);

            // extra templates
            $this->parseExtraTemplates();

            return $mapper;
        }

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper ? ['getter' => 'getShowTabs', 'setter' => 'setShowTabs'] : ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper ? ['getter' => 'getShowGroups', 'setter' => 'setShowGroups'] : ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];

        // if a grouped organization can be shapped
        // options
        $tabsOptions = null;
        if (isset($group['_options']))
        {
            $tabsOptions = $group['_options'];
            unset($group['_options']);
        }

        // content
        foreach ($group as $tab => $tabcontent) // loop on content...
            if (self::arrayDepth($tabcontent) < 1)
            {
                // direct add
                $this->addField($mapper, $tab, $tabcontent);
                $mapper->end()->end();
            } else
            {
                // groups/withs order
                $groupsOrder = null;
                if (isset($tabcontent['_options']) && isset($tabcontent['_options']['groupsOrder']))
                {
                    $groupsOrder = $tabcontent['_options']['groupsOrder'];
                    unset($tabcontent['_options']['groupsOrder']);
                }

                $endgroup = $endtab = false;

                // tab
                if ( !empty($tabcontent['_options']['hideTitle']) ||
                     $mapper instanceof ShowMapper && !$this->forceTabs )
                {
                    // display tabs as groups
                    $tabs = $this->{$fcts['tabs']['getter']}();
                    $groups = $this->{$fcts['groups']['getter']}();
                    if (isset($tabs[$tab]))
                    {
                        $tabs[$tab]['auto_created'] = true;
                        $this->{$fcts['tabs']['setter']}($tabs);

                        foreach ($groups as $groupkey => $group)
                            if (!isset($groups[$group['name']]))
                            {
                                $groups[$group['name']] = $group;
                                unset($groups[$groupkey]);
                            }
                        $this->{$fcts['groups']['setter']}($groups);
                    }
                }
                else {
                    $mapper->tab($tab, isset($tabcontent['_options']) ? $tabcontent['_options'] : []);
                    $endtab = true;
                }
                if (isset($tabcontent['_options']))
                    unset($tabcontent['_options']);

                $finalOrder = null;

                // with
                if (self::arrayDepth($tabcontent) > 0)
                    foreach ($tabcontent as $with => $withcontent)
                    {
                        $opt = isset($withcontent['_options']) ? $withcontent['_options'] : [];
                        $finalOrder = (isset($opt['fieldsOrder']) ? $opt['fieldsOrder'] : null);

                        if (!( isset($opt['hideTitle']) && $opt['hideTitle'] ))
                        {
                            $endtab = true;
                            $endgroup = true;
                            $mapper->with($with, $opt);
                        }
                        if (isset($withcontent['_options']))
                            unset($withcontent['_options']);

                        // final adds
                        if (self::arrayDepth($withcontent) > 0)
                            foreach ($withcontent as $name => $options)
                            {
                                $fieldDescriptionOptions = [];
                                if (isset($options['_options']))
                                {
                                    $fieldDescriptionOptions = $options['_options'];
                                    unset($options['_options']);
                                }
                                $this->addField($mapper, $name, $options, $fieldDescriptionOptions);
                                $endgroup = $endtab = true;
                            }

                        if ($finalOrder != null)
                            $mapper->reorder($finalOrder);

                        if ($endgroup)
                            $mapper->end();
                    }

                // order groups / withs (using tabs, because they are prioritary at the end)
                if (isset($groupsOrder))
                {
                    // preparing
                    $otabs = $mapper->getAdmin()->{$fcts['tabs']['getter']}();
                    $groups = $mapper->getAdmin()->{$fcts['groups']['getter']}();

                    // pre-ordering
                    $newgroups = [];
                    foreach ($groupsOrder as $groupname)
                    {
                        $buf = isset($otabs[$tab]) && $otabs[$tab]['auto_created'] ? "" : "$tab.";
                        if (isset($otabs[$tab]) && in_array("$buf$groupname", $otabs[$tab]['groups']))
                            $newgroups[] = "$buf$groupname";
                    }

                    // ordering tabs
                    foreach (isset($otabs[$tab]['groups']) && $otabs[$tab]['groups'] ? $otabs[$tab]['groups'] : []
                    as $groupname)
                    {
                        if (!in_array($groupname, $newgroups))
                            $newgroups[] = $groupname;
                    }
                    $otabs[$tab]['groups'] = $newgroups;

                    // "persisting"
                    $mapper->getAdmin()->{$fcts['tabs']['setter']}($otabs);
                }

                if ($endtab)
                    $mapper->end();
            }

        // ordering tabs
        if (isset($tabsOptions['tabsOrder']) && $tabs = $this->{$fcts['tabs']['getter']}())
        {
            $newtabs = [];
            foreach ($tabsOptions['tabsOrder'] as $tabname)
                if (isset($tabs[$tabname]))
                    $newtabs[$tabname] = $tabs[$tabname];
            foreach ($tabs as $tabname => $tab)
                if (!isset($newtabs[$tabname]))
                    $newtabs[$tabname] = $tab;
            $this->{$fcts['tabs']['setter']}($newtabs);
        }

        // ordering the ShowMapper
        if ($mapper instanceof ShowMapper)
        {
            $order = [];
            $groups = $this->{$fcts['groups']['getter']}();
            foreach ($this->{$fcts['tabs']['getter']}() as $tab)
                foreach ($tab['groups'] as $group)
                    if (isset($groups[$group]))
                        $order[] = $group;
            foreach ($groups as $name => $content)
                if (!in_array($name, $order))
                    $order[] = $name;

            $newgroups = [];
            foreach ($order as $grp)
                $newgroups[$grp] = $groups[$grp];
            $this->{$fcts['groups']['setter']}($newgroups);
        }

        return $mapper;
    }

    protected function addField(BaseMapper $mapper, $name, $options = [], $fieldDescriptionOptions = [])
    {

        // avoid duplicates
        if ($mapper->has($name))
            $mapper->remove($name);

        if (!is_array($options))
            $options = [];

        if (isset($options['only_new']))
        {
            if ( $options['only_new'] && !$this->subject->isNew() )
                return $mapper;
            unset($options['only_new']);
        }

        if (isset($options['only_not_new']))
        {
            if ( $options['only_not_new'] && (!$this->subject || $this->subject->isNew()) )
                return $mapper;
            unset($options['only_not_new']);
        }

        $type = null;
        if (isset($options['type']))
        {
            $type = $options['type'];
            unset($options['type']);
        }
        // save-and-remove CoreBundle-specific options
        $extras = [];
        foreach ([
            'template' => 'setTemplate',
            'initializeAssociationAdmin' => NULL,
        ] as $extra => $method)
            if (isset($fieldDescriptionOptions[$extra]))
            {
                $extras[$extra] = [$method, $fieldDescriptionOptions[$extra]];
                unset($fieldDescriptionOptions[$extra]);
            }

        $mapper->add($name, $type, $options, $fieldDescriptionOptions);

        if ($name == '_action')
        {
            $this->addListActions($mapper);
        }



        // apply extra options
        foreach ($extras as $extra => $call)
        {
            if ($call[0])
                $mapper->get($name)->{$call[0]}($call[1]);
            else
                switch ($extra) {
                    case 'initializeAssociationAdmin':
                        // only if "true"
                        if (!$call[1])
                            break;

                        // initialize the association-admin
                        $mapper->get($name)->getAssociationAdmin()->configureShowFields(new ShowMapper(
                                $mapper->get($name)->getAssociationAdmin()->getShowBuilder(), $mapper->get($name)->getAssociationAdmin()->getShow(), $mapper->get($name)->getAssociationAdmin()
                        ));

                        // set the efficient template
                        if (!isset($extras['template']))
                            $mapper->get($name)->setTemplate('LibrinfoCoreBundle:CRUD:show_association_admin.html.twig');
                        break;
                }
        }
        return $mapper;
    }

    protected function configureFields($function, BaseMapper $mapper, $class = NULL)
    {
        if (!$class)
            $class = $this->getOriginalClass();
        return $class::$function($mapper);
    }

    /**
     * @param array     $actions
     * */
    protected function addPresetBatchActions(array $actions = [])
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');

        foreach ($this->getCurrentComposition() as $class)
            if (isset($librinfo[$class]) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']))
            {
                // remove / reset
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']['_batch_action']))
                    $actions = parent::getBatchActions();

                // add
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_batch_action']))
                {
                    $buf = $librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_batch_action'];
                    foreach ($buf['actions'] as $action => $props)
                        if (substr($action, 0, 1) == '-')
                            unset($actions[substr($action, 1)]);
                        else
                        {
                            if (isset($props['translation_domain']))
                            {
                                $props['label'] = $this->trans(
                                        isset($props['label']) ? $props['label'] : 'batch_action_' . $action, array(), $props['translation_domain']
                                );
                            }
                            $actions[$action] = $props;
                        }
                }
            }

        return $actions;
    }

    /**
     * @param array     $actions
     * */
    protected function addPresetListActions(array $actions = [])
    {
        $this->_listActionLoaded = true;
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');

        foreach ($this->getCurrentComposition() as $class)
            if (isset($librinfo[$class]) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']))
            {
                // remove / reset
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']['_list_action']))
                    $this->setListActions([]);

                // add
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_list_action']))
                {
                    foreach ($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_list_action'] as $action => $props)
                        if (substr($action, 0, 1) == '-')
                            $this->removeListAction(substr($action, 1));
                        else
                        {
                            if (isset($props['translation_domain']))
                            {
                                $props['label'] = $this->trans(
                                        isset($props['label']) ? $props['label'] : 'list_action_' . $action, array(), $props['translation_domain']
                                );
                            }
                            $this->addListAction($action, $props);
                        }
                }
            }

        return $this->getListActions();
    }

    protected function addListActions($mapper)
    {
        if ($mapper->has('_actions'))
        {
            $actions = $mapper->has('_actions') ? $mapper->get('_actions')->getOptions() : array();

            $mapper->remove('_action');
            $mapper->remove('_actions');

            $mapper->add('_action', 'actions', $actions);
        }
    }

    /**
     * @param array     $formats
     * */
    protected function addPresetExportFormats(array $formats = [])
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');
        $this->exportFields = $formats;


        foreach ($this->getCurrentComposition() as $class)
            if (isset($librinfo[$class]) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']))
            {
                // remove / reset
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']['_export_format']))
                    $this->exportFields = [];

                // add
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_export_format']))
                    foreach ($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_export_format'] as $format => $fields)
                    {
                        // if no fields are defined (not an associative array)
                        if (intval($format) . '' == '' . $format && !is_array($fields))
                        {
                            $format = $fields;
                            $this->exportFields[$format] = $fields = [];
                        }

                        // if a copy of an other format is requested
                        if (!is_array($fields) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_export_format'][$fields]))
                        {
                            $librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_export_format'][$format] = // the global fields array
                                    $fields = // the local  fields array
                                    $librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_export_format'][$fields];  // the source fields array
                        }

                        // removes a specific format
                        if (substr($format, 0, 1) == '-')
                        {
                            unset($this->exportFields[substr($format, 1)]);
                            continue;
                        }

                        // if an order is defined, use it to order the extracted fields
                        if (!$fields && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_options']) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_options']['fieldsOrder']))
                        {
                            // get back default fields
                            $tmp = parent::getExportFields();
                            $fields = [];

                            // takes the ordered fields
                            foreach ($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_options']['fieldsOrder'] as $field)
                                if (in_array($field, $tmp))
                                    $fields[] = $field;

                            // then the forgotten fields as they come
                            foreach ($tmp as $field)
                                if (!in_array($field, $librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_options']['fieldsOrder']))
                                    $fields[] = $field;
                        }
                        $this->exportFields[$format] = $fields;
                    }
            }

        return $this->exportFields;
    }

    /**
     * @todo parse ShowMapper and FormMapper
     */
    protected function parseExtraTemplates()
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('librinfo');

        foreach ($this->getCurrentComposition() as $class)
            if (isset($librinfo[$class]) && isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']))
            {
                // remove / reset
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['remove']['_extra_templates']))
                {
                    // TODO
                }

                // add
                if (isset($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_extra_templates']))
                    foreach ($librinfo[$class]['Sonata\\AdminBundle\\Datagrid\\ListMapper']['add']['_extra_templates'] as $template)
                        $this->addExtraTemplate('list', $template);
            }
    }

}
