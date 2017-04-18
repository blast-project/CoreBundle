<?php

namespace Blast\CoreBundle\Admin\Traits;

use Blast\CoreBundle\Admin\CoreAdmin;
use Blast\CoreBundle\DataSource\Iterator;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait Base
{
    /**
     * @var array
     */
    protected $exportFields = array();

    /**
     * @param DatagridMapper $mapper
     */
    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        CoreAdmin::configureDatagridFilters($mapper);
    }

    /**
     * @param ListMapper $mapper
     */
    protected function configureListFields(ListMapper $mapper)
    {
        CoreAdmin::configureListFields($mapper);
    }

    /**
     * @param FormMapper $mapper
     */
    protected function configureFormFields(FormMapper $mapper)
    {
        CoreAdmin::configureFormFields($mapper);
    }

    /**
     * @param ShowMapper $mapper
     */
    protected function configureShowFields(ShowMapper $mapper)
    {
        CoreAdmin::configureShowFields($mapper);
    }

    /**
     * {@inheritdoc}
     **/
    public function getBatchActions()
    {
        $actions = array();

        if ($this->isGranted('DELETE')) {
            $actions['delete'] = array(
               'label' => 'action_delete',
               'translation_domain' => 'SonataAdminBundle',
               'ask_confirmation' => true,
           );
        }

        return $this->handleBatchActions($actions);
    }

    /**
     * {@inheritdoc}
     **/
    public function getExportFormats()
    {
        $formats = array();
        foreach (parent::getExportFormats() as $format) {
            $formats[$format] = array();
        }

        return array_keys($this->addPresetExportFormats($formats));
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFields()
    {
        // prerequisites
        $fields = parent::getExportFields();
        $this->getExportFormats();

        // nothing to add
        if (!$this->exportFields) {
            return parent::getExportFields();
        }

        // nothing specific to add
        if (!($this->getConfigurationPool()->getContainer()->get('request')->get('format')
            && isset($this->exportFields[$this->getConfigurationPool()->getContainer()->get('request')->get('format')]))) {
            return parent::getExportFields();
        }

        // specificities for this format
        return $this->exportFields[$this->getConfigurationPool()->getContainer()->get('request')->get('format')];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator()
    {
        $datagrid = $this->getDatagrid();
        $datagrid->buildPager();

        return new Iterator($datagrid->getQuery()->getQuery(), $this->getExportFields());
    }
}
