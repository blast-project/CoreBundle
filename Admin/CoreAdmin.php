<?php

namespace Blast\CoreBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Mapper\BaseMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAdmin;
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
     * Configure routes for list actions
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('duplicate', $this->getRouterIdParameter().'/duplicate');
        $collection->add('generateEntityCode');
        
        $this->removeActions($collection);
    }

    /**
     * @param DatagridMapper $mapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param ListMapper $mapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        if ( !$this->configureMapper($mapper) )
            $this->fallbackConfiguration($mapper, __FUNCTION__);
    }

    /**
     * @param FormMapper $mapper
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

    /**
     * @param string $view      'list', 'show', 'form', etc
     * @param string $template  template name
     */
    public function addExtraTemplate($view, $template)
    {
        if ( empty($this->extraTemplates[$view]) )
            $this->extraTemplates[$view] = [];
        if ( !in_array($template, $this->extraTemplates[$view]) )
            $this->extraTemplates[$view][] = $template;
    }

    /**
     * @param string $view  'list', 'show', 'form', etc
     * @return array        array of template names
     */
    public function getExtraTemplates($view)
    {
        if ( empty($this->extraTemplates[$view]) )
            $this->extraTemplates[$view] = [];
        return $this->extraTemplates[$view];
    }


    /**
     * @param string $view      'list', 'show', 'form', etc
     * @param array $link       link (array keys should be: 'label', 'url', 'class', 'title')
     */
    public function addHelperLink($view, $link)
    {
        if ( empty($this->helperLinks[$view]) )
            $this->helperLinks[$view] = [];

        // Do not add links without URL
        if (empty($link['url']))
            return;

        // Do not add two links with the same URL
        foreach ($this->helperLinks[$view] as $l)
        if ($l['url'] == $link['url'])
            return;

        $this->helperLinks[$view][] = $link;
    }

    /**
     * @param string $view  'list', 'show', 'form', etc
     * @return array        array of links (each link is an array with keys 'label', 'url', 'class' and 'title')
     */
    public function getHelperLinks($view)
    {
        if ( empty($this->helperLinks[$view]) )
            $this->helperLinks[$view] = [];
        return $this->helperLinks[$view];
    }

    /**
     * Checks if a Bundle is installed
     * @param string $bundle    Bundle name or class FQN
     */
    public function bundleExists($bundle)
    {
        $kernelBundles = $this->getConfigurationPool()->getContainer()->getParameter('kernel.bundles');
        if (array_key_exists($bundle, $kernelBundles))
            return true;
        if (in_array($bundle, $kernelBundles))
            return true;
        return false;
    }
}

