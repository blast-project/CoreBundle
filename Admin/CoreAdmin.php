<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAdmin;
use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
use Blast\Bundle\CoreBundle\Tools\Reflection\ClassAnalyzer;
use Blast\Bundle\CoreBundle\Admin\Traits\CollectionsManager;
use Blast\Bundle\CoreBundle\Admin\Traits\Mapper;
use Blast\Bundle\CoreBundle\Admin\Traits\Templates;
use Blast\Bundle\CoreBundle\Admin\Traits\PreEvents;
use Blast\Bundle\CoreBundle\Admin\Traits\ManyToManyManager;
use Blast\Bundle\CoreBundle\Admin\Traits\Actions;
use Blast\Bundle\CoreBundle\Admin\Traits\ListActions;
use Blast\Bundle\CoreBundle\CodeGenerator\CodeGeneratorRegistry;
use Blast\Bundle\CoreBundle\Translator\SilLabelTranslatorStrategy;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class CoreAdmin extends SonataAdmin implements \JsonSerializable
{
    use CollectionsManager,
        ManyToManyManager,
        Mapper,
        Templates,
        PreEvents,
        Actions,
        ListActions
    ;

    protected $extraTemplates = [];

    /**
     * @var string
     */
    protected $translationLabelPrefix = 'blast.core';

    public function configure()
    {
        parent::configure();

        $this->getLabelTranslatorStrategy()->setPrefix($this->translationLabelPrefix);
    }

    /**
     * Configure routes for list actions.
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('duplicate', $this->getRouterIdParameter() . '/duplicate');
        $collection->add('generateEntityCode');

        /* Needed or not needed ...
         * in sonata-project/admin-bundle/Controller/CRUDController.php
         * the batchAction method
         * throw exception if the http method is not POST
        */
        if ($collection->get('batch')) {
            $collection->get('batch')->setMethods(['POST']);
        }
    }

    public function getBaseRouteName()
    {
        $configuredBaseRoute = $this->getBaseRouteMapping();

        if (count($configuredBaseRoute) > 0) {
            $this->cachedBaseRouteName = null;
            if (isset($configuredBaseRoute['name']) && $this->baseRouteName === null) {
                $this->baseRouteName = $configuredBaseRoute['name'];
            }
        }

        return parent::getBaseRouteName();
    }

    public function getBaseRoutePattern()
    {
        $configuredBaseRoute = $this->getBaseRouteMapping();

        if (count($configuredBaseRoute) > 0) {
            $this->cachedBaseRoutePattern = null;
            if (isset($configuredBaseRoute['pattern']) && $this->baseRoutePattern === null) {
                $this->baseRoutePattern = $configuredBaseRoute['pattern'];
            }
        }

        return parent::getBaseRoutePattern();
    }

    // /**
    //  * Returns the baseRoutePattern used to generate the routing information.
    //  *
    //  * @throws \RuntimeException
    //  *
    //  * @return string the baseRoutePattern used to generate the routing information
    //  */
    // public function getBaseRoutePattern()
    // {
    //     $configuredBaseRoute = $this->getBaseRouteMapping();
    //
    //     if (count($configuredBaseRoute) > 0) {
    //         $this->cachedBaseRoutePattern = null;
    //         if (isset($configuredBaseRoute['pattern']) && $this->baseRoutePattern === null) {
    //             $this->baseRoutePattern = $configuredBaseRoute['pattern'];
    //         }
    //     }
    //
    //     if (null !== $this->cachedBaseRoutePattern) {
    //         return $this->cachedBaseRoutePattern;
    //     }
    //
    //     if ($this->isChild()) { // the admin class is a child, prefix it with the parent route pattern
    //         if (!$this->baseRoutePattern) {
    //             preg_match(self::CLASS_REGEX, $this->class, $matches);
    //
    //             if (!$matches) {
    //                 throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
    //             }
    //         }
    //
    //         $this->cachedBaseRoutePattern = sprintf('%s/%s/%s',
    //             $this->getParent()->getBaseRoutePattern(),
    //             $this->getParent()->getRouterIdParameter(),
    //             $this->baseRoutePattern ?: $this->urlize($matches[5], '-')
    //         );
    //     } elseif ($this->baseRoutePattern) {
    //         $this->cachedBaseRoutePattern = $this->baseRoutePattern;
    //     } else {
    //         preg_match(self::CLASS_REGEX, $this->class, $matches);
    //
    //         if (!$matches) {
    //             throw new \RuntimeException(sprintf('Please define a default `baseRoutePattern` value for the admin class `%s`', get_class($this)));
    //         }
    //
    //         $this->cachedBaseRoutePattern = sprintf('/%s%s/%s',
    //             empty($matches[1]) ? '' : $this->urlize($matches[1], '-').'/',
    //             $this->urlize($matches[3], '-'),
    //             $this->urlize($matches[5], '-')
    //         );
    //     }
    //
    //     return $this->cachedBaseRoutePattern;
    // }
    //
    // /**
    //  * Returns the baseRouteName used to generate the routing information.
    //  *
    //  * @throws \RuntimeException
    //  *
    //  * @return string the baseRouteName used to generate the routing information
    //  */
    // public function getBaseRouteName()
    // {
    //     $configuredBaseRoute = $this->getBaseRouteMapping();
    //
    //     if (count($configuredBaseRoute) > 0) {
    //         $this->cachedBaseRouteName = null;
    //         if (isset($configuredBaseRoute['name']) && $this->baseRouteName === null) {
    //             $this->baseRouteName = $configuredBaseRoute['name'];
    //         }
    //     }
    //
    //     if (null !== $this->cachedBaseRouteName) {
    //         return $this->cachedBaseRouteName;
    //     }
    //
    //     if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
    //         if (!$this->baseRouteName) {
    //             preg_match(self::CLASS_REGEX, $this->class, $matches);
    //
    //             if (!$matches) {
    //                 throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
    //             }
    //         }
    //
    //         $this->cachedBaseRouteName = sprintf('%s_%s',
    //             $this->getParent()->getBaseRouteName(),
    //             $this->baseRouteName ?: $this->urlize($matches[5])
    //         );
    //     } elseif ($this->baseRouteName) {
    //         $this->cachedBaseRouteName = $this->baseRouteName;
    //     } else {
    //         preg_match(self::CLASS_REGEX, $this->class, $matches);
    //
    //         if (!$matches) {
    //             throw new \RuntimeException(sprintf('Cannot automatically determine base route name, please define a default `baseRouteName` value for the admin class `%s`', get_class($this)));
    //         }
    //
    //         $this->cachedBaseRouteName = sprintf('admin_%s%s_%s',
    //             empty($matches[1]) ? '' : $this->urlize($matches[1]).'_',
    //             $this->urlize($matches[3]),
    //             $this->urlize($matches[5])
    //         );
    //     }
    //
    //     return $this->cachedBaseRouteName;
    // }

    public function getFormTheme()
    {
        return array_merge($this->formTheme, $this->getFormThemeMapping());
    }

    /**
     * @param DatagridMapper $mapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        if (!$this->configureMapper($mapper)) {
            $this->fallbackConfiguration($mapper, __FUNCTION__);
        }
    }

    /**
     * @param ListMapper $mapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        if (!$this->configureMapper($mapper)) {
            $this->fallbackConfiguration($mapper, __FUNCTION__);
        }
    }

    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        if (!$this->configureMapper($mapper)) {
            $this->fallbackConfiguration($mapper, __FUNCTION__);
        }
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        if (!$this->configureMapper($mapper)) {
            $this->fallbackConfiguration($mapper, __FUNCTION__);
        }
    }

    /**
     * @param BaseMapper $mapper
     */
    protected function fixShowRoutes(BaseMapper $mapper)
    {
        foreach (['getShow', 'getList'] as $fct) {
            foreach ($this->$fct()->getElements() as $field) {
                if ($field instanceof FieldDescription) {
                    $options = $field->getOptions();
                    if ($options['route']['name'] != 'edit') {
                        continue;
                    }

                    $options['route']['name'] = 'show';
                    $field->setOptions($options);
                }
            }
        }

        return $this;
    }

    protected function getCurrentComposition()
    {
        // traits of the current Entity
        $classes = ClassAnalyzer::getTraits($this->getClass());

        $interfaces = ClassAnalyzer::getInterfaces($this->getClass());

        // implementations of the current Entity
        foreach (array_reverse($interfaces) as $interface) {
            $classes[] = $interface;
        }

        // inheritance of the current Entity
        foreach (array_reverse([$this->getClass()] + class_parents($this->getClass())) as $class) {
            $classes[] = $class;
        }

        // inheritance of the current Admin
        foreach (array_reverse([$this->getOriginalClass()] + $this->getParentClasses()) as $admin) {
            $classes[] = $admin;
        }

        return $classes;
    }

    private function fallbackConfiguration(BaseMapper $mapper, $function)
    {
        // fallback
        $rm = new \ReflectionMethod($this->getParentClass(), $function);
        if ($rm->class == $this->getParentClass()) {
            $this->configureFields($function, $mapper, $this->getParentClass());
        }
    }

    /**
     * Returns the level of depth of an array.
     *
     * @param array $array
     * @param int   $level : do not use, just used for recursivity
     *
     * @return int : depth
     */
    private static function arrayDepth($array, $level = 0)
    {
        if (!$array) {
            return $level;
        }

        if (!is_array($array)) {
            return $level;
        }

        ++$level;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $level = $level < self::arrayDepth($value, $level) ? self::arrayDepth($value, $level) : $level;
            }
        }

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

    /**
     * @param string $view     'list', 'show', 'form', etc
     * @param string $template template name
     */
    public function addExtraTemplate($view, $template)
    {
        if (empty($this->extraTemplates[$view])) {
            $this->extraTemplates[$view] = [];
        }
        if (!in_array($template, $this->extraTemplates[$view])) {
            $this->extraTemplates[$view][] = $template;
        }
    }

    /**
     * @param string $view 'list', 'show', 'form', etc
     *
     * @return array array of template names
     */
    public function getExtraTemplates($view)
    {
        if (empty($this->extraTemplates[$view])) {
            $this->extraTemplates[$view] = [];
        }

        return $this->extraTemplates[$view];
    }

    /**
     * @param string $view 'list', 'show', 'form', etc
     * @param array  $link link (array keys should be: 'label', 'url', 'class', 'title')
     */
    public function addHelperLink($view, $link)
    {
        if (empty($this->helperLinks[$view])) {
            $this->helperLinks[$view] = [];
        }

        // Do not add links without URL
        if (empty($link['url'])) {
            return;
        }

        // Do not add two links with the same URL
        foreach ($this->helperLinks[$view] as $l) {
            if ($l['url'] == $link['url']) {
                return;
            }
        }

        $this->helperLinks[$view][] = $link;
    }

    /**
     * @param string $view 'list', 'show', 'form', etc
     *
     * @return array array of links (each link is an array with keys 'label', 'url', 'class' and 'title')
     */
    public function getHelperLinks($view)
    {
        if (empty($this->helperLinks[$view])) {
            $this->helperLinks[$view] = [];
        }

        return $this->helperLinks[$view];
    }

    /**
     * Checks if a Bundle is installed.
     *
     * @param string $bundle Bundle name or class FQN
     */
    public function bundleExists($bundle)
    {
        $kernelBundles = $this->getConfigurationPool()->getContainer()->getParameter('kernel.bundles');
        if (array_key_exists($bundle, $kernelBundles)) {
            return true;
        }
        if (in_array($bundle, $kernelBundles)) {
            return true;
        }

        return false;
    }

    /**
     * Rename a form tab after form fields have been configured.
     *
     * TODO: groups of the renamed tab are still prefixed with the old tab name
     *
     * @param type $tabName    the name of the tab to be renamed
     * @param type $newTabName the new name for the tab
     */
    public function renameFormTab($tabName, $newTabName, $keepOrder = true)
    {
        $tabs = $this->getFormTabs();

        if (!$tabs) {
            return;
        }

        if (!isset($tabs[$tabName])) {
            throw new \Exception(sprintf('Tab %s does not exist.', $tabName));
        }
        if (isset($tabs[$newTabName])) {
            return;
        }

        if ($keepOrder) {
            $keys = array_keys($tabs);
            $keys[array_search($tabName, $keys)] = $newTabName;
            $tabs = array_combine($keys, $tabs);
        } else {
            $tabs[$newTabName] = $tabs[$tabName];
            unset($tabs[$tabName]);
        }

        $this->setFormTabs($tabs);
    }

    /**
     * Rename a show tab after show fields have been configured.
     *
     * TODO: groups of the renamed tab are still prefixed with the old tab name
     *
     * @param type $tabName    the name of the tab to be renamed
     * @param type $newTabName the new name for the tab
     */
    public function renameShowTab($tabName, $newTabName, $keepOrder = true)
    {
        $tabs = $this->getShowTabs();

        if (!$tabs) {
            return;
        }

        if (!isset($tabs[$tabName])) {
            throw new \Exception(sprintf('Tab %s does not exist.', $tabName));
        }
        if (isset($tabs[$newTabName])) {
            return;
        }

        if ($keepOrder) {
            $keys = array_keys($tabs);
            $keys[array_search($tabName, $keys)] = $newTabName;
            $tabs = array_combine($keys, $tabs);
        } else {
            $tabs[$newTabName] = $tabs[$tabName];
            unset($tabs[$tabName]);
        }

        $this->setShowTabs($tabs);
    }

    /**
     * Rename a form group.
     *
     * @param string $group        the old group name
     * @param string $tab          the tab the group belongs to
     * @param string $newGroupName the new group name
     *
     * @return self
     */
    public function renameFormGroup($group, $tab, $newGroupName)
    {
        $groups = $this->getFormGroups();

        // When the default tab is used, the tabname is not prepended to the index in the group array
        if ($tab !== 'default') {
            $group = $tab . '.' . $group;
        }
        $newGroup = ($tab !== 'default') ? $tab . '.' . $newGroupName : $newGroupName;

        if (isset($groups[$newGroup])) {
            throw new \Exception(sprintf('%s form group already exists.', $newGroup));
        }
        if (!array_key_exists($group, $groups)) {
            throw new \Exception(sprintf('form group « %s » doesn\'t exist.', $group));
        }

        $groups[$newGroup] = $groups[$group];
        $groups[$newGroup]['name'] = $newGroupName;
        unset($groups[$group]);

        $tabs = $this->getFormTabs();
        $key = array_search($group, $tabs[$tab]['groups']);

        if (false !== $key) {
            $tabs[$tab]['groups'][$key] = $newGroup;
        }

        $this->setFormTabs($tabs);
        $this->setFormGroups($groups);

        return $this;
    }

    /**
     * Removes tab in current form Mapper.
     *
     * @param string|array $tabNames name or array of names of tabs to be removed
     * @param FormMapper   $mapper   Sonata Admin form mapper
     */
    public function removeTab($tabNames, $mapper)
    {
        $currentTabs = $this->getFormTabs();
        foreach ($currentTabs as $k => $item) {
            if (is_array($tabNames) && in_array($item['name'], $tabNames) || !is_array($tabNames) && $item['name'] === $tabNames) {
                foreach ($item['groups'] as $groupName) {
                    $this->removeAllFieldsFromFormGroup($groupName, $mapper);
                }
                unset($currentTabs[$k]);
            }
        }
        $this->setFormTabs($currentTabs);
    }

    /**
     * Removes all fields from form groups and remove them from mapper.
     *
     * @param string     $groupName Name of the group to remove
     * @param FormMapper $mapper    Sonata Admin form mapper
     */
    public function removeAllFieldsFromFormGroup($groupName, $mapper)
    {
        $formGroups = $this->getFormGroups();
        foreach ($formGroups as $name => $formGroup) {
            if ($name === $groupName) {
                foreach ($formGroups[$name]['fields'] as $key => $field) {
                    $mapper->remove($key);
                }
            }
        }
    }

    public function jsonSerialize()
    {
        $propertiesToShow = [
            'baseRouteName',
            'baseRoutePattern',
            'extraTemplates',
            'listFieldDescriptions',
            'showFieldDescriptions',
            'formFieldDescriptions',
            'filterFieldDescriptions',
            'maxPerPage',
            'maxPageLinks',
            'classnameLabel',
            'translationDomain',
            'formOptions',
            'datagridValues',
            'perPageOptions',
            'pagerType',
            'code',
            'label',
            'routes',
            'subject',
            'children',
            'parent',
            'baseCodeRoute',
            'uniqid',
            'extensions',
            'class',
            'subClasses',
            'list',
            'show',
            'form',
            'filter',
            'formGroups',
            'formTabs',
            'showGroups',
            'showTabs',
            'managedCollections',
            'helperLinks',
            'titles',
        ];

        $properties = [];
        foreach ($this as $key => $value) {
            if (in_array($key, $propertiesToShow)) {
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);

        $hasCodeGenerator = CodeGeneratorRegistry::hasGeneratorForClass(get_class($object));
        if ($hasCodeGenerator) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach (CodeGeneratorRegistry::getCodeGenerators(get_class($object)) as $name => $generator) {
                $accessor->setValue($object, $name, $generator->generate($object));
            }
        }
    }

    public function getFlashManager()
    {
        return $this->getConfigurationPool()->getContainer()->get('sonata.core.flashmessage.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function preBatchAction($actionName, ProxyQueryInterface $query, array &$idx, $allElements)
    {
        parent::preBatchAction($actionName, $query, $idx, $allElements);

        if ($actionName === 'delete') {
            $cascadingRelationChecker = $this->getConfigurationPool()->getContainer()->get('blast_core.doctrine.orm.cascading_relation_checker');

            foreach ($idx as $id) {
                $entity = $this->getModelManager()->find($this->getClass(), $id);

                if ($entity !== null) {
                    $undeletableAssociations = $cascadingRelationChecker->beforeEntityDelete($entity, $idx);

                    if (count($undeletableAssociations) > 0) {
                        foreach ($undeletableAssociations as $key => $undeletableAssociation) {
                            $undeletableAssociations[$key] = $this->getConfigurationPool()->getContainer()->get('translator')->trans('blast.doctrine_relations.' . $undeletableAssociation, [], 'messages');
                        }

                        $errorMessage = 'Cannot delete "%entity%" because it has remaining relation(s) %relations%';

                        $message = $this->getTranslator()->trans(
                            $errorMessage,
                            [
                                '%relations%' => trim(implode(', ', $undeletableAssociations)),
                                '%entity%'    => (string) $entity,
                            ],
                            'SonataCoreBundle'
                        );

                        $this->getConfigurationPool()->getContainer()->get('session')->getFlashBag()->add('warning', $message);
                    }
                }
            }
        }
    }
}
