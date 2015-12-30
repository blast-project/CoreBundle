<?php

namespace Librinfo\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait Base
{
    /**
     * @param DatagridMapper $mapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
    }

    /**
     * @param ListMapper $mapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
    }

    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        $this->configureFields(__FUNCTION__, $mapper, $this->getGrandParentClass());
    }
    
    /**
     * {@inheritdoc}
     **/
    public function getBatchActions()
    {
        return $this->addPresetBatchActions(parent::getBatchActions());
    }
    
    /**
     * {@inheritdoc}
     **/
    public function getExportFormats()
    {
        return $this->addPresetExportFormats(parent::getExportFormats());
    }
}

