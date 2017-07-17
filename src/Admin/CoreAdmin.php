<?php

/*
 * This file is part of the Blast Project package.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\CoreBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAdmin;
use Sonata\DoctrineORMAdminBundle\Admin\FieldDescription;
use Blast\CoreBundle\Tools\Reflection\ClassAnalyzer;
use Blast\CoreBundle\Admin\Traits\CollectionsManager;
use Blast\CoreBundle\Admin\Traits\Mapper;
use Blast\CoreBundle\Admin\Traits\Templates;
use Blast\CoreBundle\Admin\Traits\PreEvents;
use Blast\CoreBundle\Admin\Traits\ManyToManyManager;
use Blast\CoreBundle\Admin\Traits\Actions;
use Blast\CoreBundle\Admin\Traits\ListActions;

abstract class CoreAdmin extends SonataAdmin
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
     * Configure routes for list actions.
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('duplicate', $this->getRouterIdParameter() . '/duplicate');
        $collection->add('generateEntityCode');
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
}
