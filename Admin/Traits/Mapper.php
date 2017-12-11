<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Exception;

trait Mapper
{
    /**
     * Force tabulations on Show views.
     *
     * @var bool
     */
    protected $forceTabs = false;

    /**
     * Links in the view navbar.
     *
     * @var array
     */
    protected $helperLinks = [];

    /**
     * Admin titles (for list, show, edit and create).
     *
     * @var string
     */
    public $titles = [];

    /**
     * Admin title templates (for list, show, edit and create).
     *
     * @var array
     */
    public $titleTemplates = [];

    protected function configureMapper(BaseMapper $mapper)
    {
        $classes = $this->getCurrentComposition();
        $blast = $this
            ->getConfigurationPool()
            ->getContainer()
            ->getParameter('blast')
        ;
        $this
            ->getConfigurationPool()
            ->getContainer()
            ->get('logger')
            ->debug(sprintf(
                '[BlastCoreBundle] Processing the configuration in this order: %s',
                 implode(', ', $classes)
            ))
        ;

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper ?
                ['getter' => 'getShowTabs', 'setter' => 'setShowTabs'] :
                ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper ?
                ['getter' => 'getShowGroups', 'setter' => 'setShowGroups'] :
                ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];

        // Figure out if we have to display tabs on the Show view
        $this->forceTabs = false;
        if ($mapper instanceof ShowMapper) {
            foreach ($classes as $class) {
                if (isset($blast[$class])) {
                    foreach (array_reverse($list = array_merge([get_class($mapper)], array_values(class_parents($mapper)))) as $mapper_class) {
                        if (!empty($blast[$class][$mapper_class]['forceTabs'])) {
                            $this->forceTabs = true;
                        }
                    }
                }
            }
        }

        // builds the configuration, based on the Mapper class
        foreach ($classes as $class) {
            if (!isset($blast[$class])) {
                continue;
            }

            // copy stuff from elsewhere
            foreach (array_reverse($list = array_merge([get_class($mapper)], array_values(class_parents($mapper)))) as $mapper_class) {
                if (isset($blast[$class][$mapper_class]) && !empty($blast[$class][$mapper_class]['_copy'])) {
                    if (!is_array($blast[$class][$mapper_class]['_copy'])) {
                        $blast[$class][$mapper_class]['_copy'] = [$blast[$class][$mapper_class]['_copy']];
                    }
                    foreach ($blast[$class][$mapper_class]['_copy'] as $copy) {
                        $list = array_merge(
                                $list, array_merge([$copy], array_values(class_parents($copy)))
                        );
                    }
                }
            }

            $specialKeys = ['_actions', '_list_actions', '_batch_actions', '_export_formats', '_extra_templates', '_helper_links'];

            // process data...
            foreach (array_reverse($list) as $mapper_class) {
                if (!isset($blast[$class][$mapper_class])) {
                    continue;
                }

                // remove fields
                if (isset($blast[$class][$mapper_class]['remove'])) {
                    if (isset($blast['all'][$mapper_class]['remove'])) {
                        $blast[$class][$mapper_class]['remove'] = array_merge_recursive(
                            $blast[$class][$mapper_class]['remove'],
                            $blast['all'][$mapper_class]['remove']
                        );
                    }

                    foreach ($blast[$class][$mapper_class]['remove'] as $key => $field) {
                        if (in_array($key, $specialKeys)) {
                            continue;
                        }

                        if ($mapper->has($key)) {
                            $mapper->remove($key);

                            // compensating the partial removal in Sonata Admin, that does not touch the groups when removing a field
                            if ($mapper instanceof BaseGroupedMapper) {
                                foreach ($groups = $this->{$fcts['groups']['getter']}() as $groupkey => $group) {
                                    if (isset($group['fields'][$key])) {
                                        unset($groups[$groupkey]['fields'][$key]);
                                        if (!$groups[$groupkey]['fields']) {
                                            unset($groups[$groupkey]);
                                        }
                                        $this->{$fcts['groups']['setter']}($groups);
                                    }
                                }
                            }
                        }
                    }
                }

                // add fields & more
                if (isset($blast[$class][$mapper_class]['add'])) {
                    if (isset($blast['all'][$mapper_class]['add'])) {
                        $blast[$class][$mapper_class]['add'] = array_merge(
                                    $blast[$class][$mapper_class]['add'],
                                    $blast['all'][$mapper_class]['add']
                                );
                    }

                    // do not parse _batch_actions & co
                    foreach ($specialKeys as $sk) {
                        if (isset($blast[$class][$mapper_class]['add'][$sk])) {
                            unset($blast[$class][$mapper_class]['add'][$sk]);
                        }
                    }

                    $this->addContent($mapper, $blast[$class][$mapper_class]['add']);
                }

                // set Admin titles
                $titleTemplate = isset($blast[$class][$mapper_class]['titleTemplate']) ? $blast[$class][$mapper_class]['titleTemplate'] : null;
                $title = isset($blast[$class][$mapper_class]['title']) ? $blast[$class][$mapper_class]['title'] : null;
                $this->setTitles($mapper, $titleTemplate, $title);
            }
        }

        if ($mapper instanceof BaseGroupedMapper) { // ShowMapper and FormMapper
            // removing empty groups
            $groups = $this->{$fcts['groups']['getter']}();
            if (is_array($groups)) {
                foreach ($groups as $groupkey => $group) {
                    if (!$group['fields']) {
                        unset($groups[$groupkey]);
                    }
                }
                $this->{$fcts['groups']['setter']}($groups);
            }

            // removing empty tabs
            $tabs = $this->{$fcts['tabs']['getter']}();
            if (is_array($tabs)) {
                foreach ($tabs as $tabkey => $tab) {
                    foreach ($tab['groups'] as $groupkey => $group) {
                        if (!isset($this->{$fcts['groups']['getter']}()[$group])) {
                            unset($tabs[$tabkey]['groups'][$groupkey]);
                        }
                    }

                    if (!$tabs[$tabkey]['groups']) {
                        unset($tabs[$tabkey]);
                    }
                }
                $this->{$fcts['tabs']['setter']}($tabs);
            }
        }

        $this->fixTemplates($mapper);

        if (!$mapper instanceof FormMapper) {
            $this->fixShowRoutes($mapper);
        }

        // Debug profiler
        $this->getConfigurationPool()->getContainer()->get('blast_core.profiler.collector')
            ->collectOnce('Mapper', $mapper)
            ->collectOnce('Managed classes', $classes);

        return $this;
    }

    /**
     * @param BaseMapper $mapper
     * @param array      $group
     *
     * @return BaseMapper
     */
    protected function addContent(BaseMapper $mapper, $group)
    {
        // helper links
        $this->parseHelperLinks();

        // flat organization (DatagridMapper / ListMapper...)
        if (!$mapper instanceof BaseGroupedMapper) {
            //list actions
            $this->addActions();
            $this->removeActions();

            // options pre-treatment
            $options = [];
            if (isset($group['_options'])) {
                $options = $group['_options'];
                unset($group['_options']);
            }

            // content
            foreach ($group as $add => $opts) {
                $this->addField($mapper, $add, $opts);
            }

            // options
            if (isset($options['fieldsOrder'])) {
                $mapper->reorder($options['fieldsOrder']);
            }

            // extra templates
            $this->parseExtraTemplates();

            return $mapper;
        }

        $fcts = [
            'tabs' => $mapper instanceof ShowMapper ?
            ['getter' => 'getShowTabs', 'setter' => 'setShowTabs'] :
            ['getter' => 'getFormTabs', 'setter' => 'setFormTabs'],
            'groups' => $mapper instanceof ShowMapper ?
            ['getter' => 'getShowGroups', 'setter' => 'setShowGroups'] :
            ['getter' => 'getFormGroups', 'setter' => 'setFormGroups'],
        ];

        // if a grouped organization can be shapped
        // options
        $tabsOptions = null;
        if (isset($group['_options'])) {
            $tabsOptions = $group['_options'];
            unset($group['_options']);
        }

        // content
        foreach ($group as $tab => $tabcontent) { // loop on content...
            if (self::arrayDepth($tabcontent) < 1) {
                // direct add
                $this->addField($mapper, $tab, $tabcontent);
                $mapper->end()->end();
            } else {
                // groups/withs order
                $groupsOrder = null;
                if (isset($tabcontent['_options']['groupsOrder'])) {
                    $groupsOrder = $tabcontent['_options']['groupsOrder'];
                    unset($tabcontent['_options']['groupsOrder']);
                }

                $endgroup = $endtab = false;

                // tab
                if (!empty($tabcontent['_options']['hideTitle']) || $mapper instanceof ShowMapper && !$this->forceTabs) {
                    // display tabs as groups
                    $tabs = $this->{$fcts['tabs']['getter']}();
                    $groups = $this->{$fcts['groups']['getter']}();
                    if (isset($tabs[$tab])) {
                        $tabs[$tab]['auto_created'] = true;
                        $this->{$fcts['tabs']['setter']}($tabs);

                        foreach ($groups as $groupkey => $group) {
                            if (!isset($groups[$group['name']])) {
                                $groups[$group['name']] = $group;
                                unset($groups[$groupkey]);
                            }
                        }
                        $this->{$fcts['groups']['setter']}($groups);
                    }
                } else {
                    $mapper->tab($tab, isset($tabcontent['_options']) ? $tabcontent['_options'] : []);
                    $endtab = true;
                }

                // adding count of collections items in tab
                if (isset($tabcontent['_options']['countChildItems']) && is_array($tabcontent['_options']['countChildItems'])) {
                    $tabs = $this->{$fcts['tabs']['getter']}();
                    if (strpos($tabs[$tab]['class'], 'countable-tab') === false) {
                        $tabs[$tab]['class'] .= ' countable-tab';

                        foreach ($tabcontent['_options']['countChildItems'] as $fieldToCount) {
                            if (strpos($tabs[$tab]['class'], 'count-' . $fieldToCount) === false) {
                                $tabs[$tab]['class'] .= ' count-' . $fieldToCount;
                            }
                        }

                        $this->{$fcts['tabs']['setter']}($tabs);
                    }
                }

                // clearing tabcontent options
                if (isset($tabcontent['_options'])) {
                    unset($tabcontent['_options']);
                }

                $finalOrder = null;

                // with
                if (self::arrayDepth($tabcontent) > 0) {
                    foreach ($tabcontent as $with => $withcontent) {
                        $opt = isset($withcontent['_options']) ? $withcontent['_options'] : [];
                        $finalOrder = (isset($opt['fieldsOrder']) ? $opt['fieldsOrder'] : null);

                        if (empty($opt['hideTitle'])) {
                            $endtab = true;
                            $endgroup = true;
                            $mapper->with($with, $opt);
                        }
                        if (isset($withcontent['_options'])) {
                            unset($withcontent['_options']);
                        }

                        // final adds
                        if (self::arrayDepth($withcontent) > 0) {
                            foreach ($withcontent as $name => $options) {
                                $fieldDescriptionOptions = [];
                                if (isset($options['_options'])) {
                                    $fieldDescriptionOptions = $options['_options'];
                                    unset($options['_options']);
                                }
                                $this->addField($mapper, $name, $options, $fieldDescriptionOptions);
                                $endgroup = $endtab = true;
                            }
                        }

                        if ($finalOrder != null) {
                            $mapper->reorder($finalOrder);
                        }

                        if ($endgroup) {
                            $mapper->end();
                        }
                    }
                }

                // order groups / withs (using tabs, because they are prioritary at the end)
                if (isset($groupsOrder)) {
                    // preparing
                    $otabs = $mapper->getAdmin()->{$fcts['tabs']['getter']}();
                    $groups = $mapper->getAdmin()->{$fcts['groups']['getter']}();

                    // pre-ordering
                    $newgroups = [];
                    $buf = empty($otabs[$tab]['auto_created']) ? "$tab." : '';
                    foreach ($groupsOrder as $groupname) {
                        if (isset($otabs[$tab]) && in_array("$buf$groupname", $otabs[$tab]['groups'])) {
                            $newgroups[] = "$buf$groupname";
                        }
                    }

                    // ordering tabs
                    foreach (empty($otabs[$tab]['groups']) ? [] : $otabs[$tab]['groups'] as $groupname) {
                        if (!in_array($groupname, $newgroups)) {
                            $newgroups[] = $groupname;
                        }
                    }
                    $otabs[$tab]['groups'] = $newgroups;

                    // "persisting"
                    $mapper->getAdmin()->{$fcts['tabs']['setter']}($otabs);
                }

                if ($endtab) {
                    $mapper->end();
                }
            }
        }

        // ordering tabs
        if (isset($tabsOptions['tabsOrder']) && $tabs = $this->{$fcts['tabs']['getter']}()) {
            $newtabs = [];
            foreach ($tabsOptions['tabsOrder'] as $tabname) {
                if (isset($tabs[$tabname])) {
                    $newtabs[$tabname] = $tabs[$tabname];
                }
            }
            foreach ($tabs as $tabname => $tab) {
                if (!isset($newtabs[$tabname])) {
                    $newtabs[$tabname] = $tab;
                }
            }
            $this->{$fcts['tabs']['setter']}($newtabs);
        }

        // ordering the ShowMapper
        if ($mapper instanceof ShowMapper) {
            foreach ($group as $tabName => $tabContent) {
                $tabOptions = null;
                if (isset($tabContent['_options'])) {
                    $tabOptions = $tabContent['_options'];
                    unset($tabContent['_options']);
                }

                if (isset($tabOptions['groupsOrder'])) {
                    $tabs = $this->{$fcts['tabs']['getter']}();
                    $groups = $this->{$fcts['groups']['getter']}();

                    $groupOrder = $tabOptions['groupsOrder'];

                    $properOrderedArray = array_merge(array_flip($groupOrder), $groups);

                    $this->{$fcts['groups']['setter']}($properOrderedArray);
                    $this->{$fcts['tabs']['setter']}($tabs);
                }
            }
        }

        return $mapper;
    }

    protected function addField(BaseMapper $mapper, $name, $options = [], $fieldDescriptionOptions = [])
    {
        // avoid duplicates
        if ($mapper->has($name)) {
            $mapper->remove($name);
        }

        if (!is_array($options)) {
            $options = [];
        }

        if (isset($options['only_new'])) {
            if ($options['only_new'] && $this->subject && !$this->subject->isNew()) {
                return $mapper;
            }
            unset($options['only_new']);
        }

        if (isset($options['only_not_new'])) {
            if ($options['only_not_new'] && (!$this->subject || $this->subject->isNew())) {
                return $mapper;
            }
            unset($options['only_not_new']);
        }

        $type = null;
        if (isset($options['type'])) {
            $type = $options['type'];
            unset($options['type']);
        }

        if (isset($options['constraints'])) {
            foreach ($options['constraints'] as $k => $constraint) {
                $options['constraints'][$k] = new $constraint();
            }
        }

        if (isset($options['required']) && $options['required'] === true) {
            $options['constraints'] = [new NotBlank()];
        }

        if (isset($options['query'])) {
            $this->manageQueryCallback($mapper, $options);
        }

        if (isset($options['choicesCallback'])) {
            $this->manageChoicesCallback($mapper, $options);
        }

        if (isset($options['serviceCallback'])) {
            $this->manageServiceCallback($mapper, $options);
        }

        // save-and-remove CoreBundle-specific options
        $extras = [];
        foreach ([
            'template' => 'setTemplate',
            'initializeAssociationAdmin' => null,
        ] as $extra => $method) {
            if (isset($fieldDescriptionOptions[$extra])) {
                $extras[$extra] = [$method, $fieldDescriptionOptions[$extra]];
                unset($fieldDescriptionOptions[$extra]);
            }
        }

        $mapper->add($name, $type, $options, $fieldDescriptionOptions);

        // apply extra options
        foreach ($extras as $extra => $call) {
            if ($call[0]) {
                $mapper->get($name)->{$call[0]}($call[1]);
            } else {
                switch ($extra) {
                    case 'initializeAssociationAdmin':
                        // only if "true"
                        if (!$call[1]) {
                            break;
                        }

                        // initialize the association-admin
                        $mapper->get($name)->getAssociationAdmin()->configureShowFields(new ShowMapper(
                                $mapper->get($name)->getAssociationAdmin()->getShowBuilder(), $mapper->get($name)->getAssociationAdmin()->getShow(), $mapper->get($name)->getAssociationAdmin()
                        ));

                        // set the efficient template
                        if (!isset($extras['template'])) {
                            $mapper->get($name)->setTemplate('BlastCoreBundle:CRUD:show_association_admin.html.twig');
                        }
                        break;
                }
            }
        }

        return $mapper;
    }

    protected function configureFields($function, BaseMapper $mapper, $class = null)
    {
        if (!$class) {
            $class = $this->getOriginalClass();
        }

        return $class::$function($mapper);
    }

    /**
     * @param array $actions
     * */
    protected function handleBatchActions(array $actions = [])
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        $mapperClass = ListMapper::class;
        $actionKey = '_batch_actions';

        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class][$mapperClass])) {
                $config = $blast[$class][$mapperClass];

                if (isset($blast['all'][$mapperClass])) {
                    $config = array_merge_recursive(
                        $config,
                        $blast['all'][$mapperClass]
                    );
                }

                // remove / reset
                if (isset($config['remove'][$actionKey])) {
                    $actions = parent::getBatchActions();

                    foreach ($config['remove'][$actionKey] as $action) {
                        if (isset($actions[$action])) {
                            unset($actions[$action]);
                        }
                    }
                }

                // add
                if (isset($config['add'][$actionKey])) {
                    $buf = $config['add'][$actionKey];

                    foreach ($buf as $action => $props) {
                        $name = 'batch_action_' . $action;

                        foreach ([
                            'label' => $name,
                            'params' => [],
                            'translation_domain' => $this->getTranslationDomain(),
                            'action' => $name,
                            'route' => 'batch_' . $action,
                        ] as $field => $value) {
                            if (empty($props[$field])) {
                                $props[$field] = $value;
                            }
                        }

                        $actions[$action] = $props;
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * @param array $actions
     * */
    protected function handleListActions(array $actions = [])
    {
        $this->_listActionLoaded = true;
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            // remove / reset
            if (isset($blast[$class][ListMapper::class]['remove']['_list_actions'])) {
                foreach ($blast[$class][ListMapper::class]['remove']['_list_actions'] as $action) {
                    $this->removeListAction($action);
                }
            }

            // add
            if (isset($blast[$class][ListMapper::class]['add']['_list_actions'])) {
                foreach ($blast[$class][ListMapper::class]['add']['_list_actions'] as $action => $props) {
                    $props['translation_domain'] = isset($props['translation_domain']) ? $props['translation_domain'] : $this->getTranslationDomain();
                    $this->addListAction($action, $props);
                }
            }
        }

        return $this->getListActions();
    }

    /**
     * @param array $formats
     * */
    protected function addPresetExportFormats(array $formats = [])
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        $this->exportFields = $formats;

        foreach ($this->getCurrentComposition() as $class) {
            // remove / reset
            if (isset($blast[$class][ListMapper::class]['remove']['_export_format'])) {
                $this->exportFields = [];
            }

            // add
            if (isset($blast[$class][ListMapper::class]['add']['_export_format'])) {
                foreach ($blast[$class][ListMapper::class]['add']['_export_format'] as $format => $fields) {
                    // if no fields are defined (not an associative array)
                    if (intval($format) . '' == '' . $format && !is_array($fields)) {
                        $format = $fields;
                        $this->exportFields[$format] = $fields = [];
                    }

                    // if a copy of an other format is requested
                    if (!is_array($fields) && isset($blast[$class][ListMapper::class]['add']['_export_format'][$fields])) {
                        $blast[$class][ListMapper::class]['add']['_export_format'][$format] = // the global fields array
                                $fields = // the local  fields array
                                $blast[$class][ListMapper::class]['add']['_export_format'][$fields];  // the source fields array
                    }

                    // removes a specific format
                    if (substr($format, 0, 1) == '-') {
                        unset($this->exportFields[substr($format, 1)]);
                        continue;
                    }

                    // if an order is defined, use it to order the extracted fields
                    if (!$fields && isset($blast[$class][ListMapper::class]['add']['_options']['fieldsOrder'])) {
                        // get back default fields
                        $tmp = parent::getExportFields();
                        $fields = [];

                        // takes the ordered fields
                        foreach ($blast[$class][ListMapper::class]['add']['_options']['fieldsOrder'] as $field) {
                            if (in_array($field, $tmp)) {
                                $fields[] = $field;
                            }
                        }

                        // then the forgotten fields as they come
                        foreach ($tmp as $field) {
                            if (!in_array($field, $blast[$class][ListMapper::class]['add']['_options']['fieldsOrder'])) {
                                $fields[] = $field;
                            }
                        }
                    }
                    $this->exportFields[$format] = $fields;
                }
            }
        }

        return $this->exportFields;
    }

    /**
     * @todo parse ShowMapper and FormMapper
     */
    protected function parseExtraTemplates()
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            // remove / reset
            if (isset($blast[$class][ListMapper::class]['remove']['_extra_templates'])) {
                // TODO
            }

            // add
            if (isset($blast[$class][ListMapper::class]['add']['_extra_templates'])) {
                foreach ($blast[$class][ListMapper::class]['add']['_extra_templates'] as $template) {
                    $this->addExtraTemplate('list', $template);
                }
            }
        }
    }

    protected function parseHelperLinks()
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        $mappers = [
            'list' => ListMapper::class,
            'show' => ShowMapper::class,
            'form' => FormMapper::class,
        ];

        foreach ($this->getCurrentComposition() as $class) {
            foreach ($mappers as $mapper => $mapper_class) {
                // remove / reset
                if (isset($blast[$class][$mapper_class]['remove']['_helper_links'])) {
                    // TODO
                }

                // add
                if (isset($blast[$class][$mapper_class]['add']['_helper_links'])) {
                    foreach ($blast[$class][$mapper_class]['add']['_helper_links'] as $link) {
                        $this->addHelperLink($mapper, $link);
                    }
                }
            }
        }
    }

    protected function setTitles(BaseMapper $mapper, $titleTemplate, $title)
    {
        $contexts = [
            ListMapper::class => 'list',
            ShowMapper::class => 'show',
            FormMapper::class => 'form',
        ];
        if (!isset($contexts[get_class($mapper)])) {
            return;
        }

        $context = $contexts[get_class($mapper)];
        if ($titleTemplate) {
            $this->titleTemplates[$context] = $titleTemplate;
        }
        if ($title) {
            $this->titles[$context] = $title;
        }
    }

    protected function getFormThemeMapping()
    {
        $theme = [];
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class])) {
                if (isset($blast[$class]['form_theme'])) {
                    $theme = array_merge($theme, $blast[$class]['form_theme']);
                }
            }
        }

        return $theme;
    }

    protected function getBaseRouteMapping()
    {
        $baseRoute = [];
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class]) && isset($blast[$class]['baseRoute'])) {
                $reflexionClass = new \ReflectionClass($class);
                if (!$reflexionClass->isTrait()) {
                    $baseRoute = array_merge($baseRoute, $blast[$class]['baseRoute']);
                }
            }
        }

        return $baseRoute;
    }

    protected function manageCallback($mapper, &$options, $callbackType)
    {
        $option = $options[$callbackType];

        $entityClass = isset($options['class']) ? $options['class'] : $this->getClass();

        if (!is_array($option)) {
            // @TODO: This is outdated,
            throw new Exception('« $callbackType » option must be an array : ["FQDN"=>"static method name"]');
        }

        list($serviceNameOrClass, $methodName) = $option;

        $targetOption = (isset($option[2]) ? $option[2] : null);

        if ($this->getConfigurationPool()->getContainer()->has($serviceNameOrClass)) {
            $callBackFunction = [$this->getConfigurationPool()->getContainer()->get($serviceNameOrClass), $methodName];
        } else {
            $callBackFunction = call_user_func($serviceNameOrClass . '::' . $methodName, $this->getModelManager(), $entityClass);
        }

        if ($targetOption !== null) {
            $options[$targetOption] = $callBackFunction;
            unset($options[$callbackType]);
        }

        return $callBackFunction;
    }

    protected function manageQueryCallback($mapper, &$options)
    {
        $callback = $this->manageCallback($mapper, $options, 'query');
        $options['query'] = $callback;
    }

    protected function manageChoicesCallback($mapper, &$options)
    {
        $callback = $this->manageCallback($mapper, $options, 'choicesCallback');

        $options['choices'] = $callback;
        $options['choice_loader'] = new CallbackChoiceLoader(function () use ($options) {
            return $options['choices'];
        });
        unset($options['choicesCallback']);
    }

    public function manageServiceCallback($mapper, &$options)
    {
        $this->manageCallback($mapper, $options, 'serviceCallback');
    }
}
