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

use Blast\Bundle\CoreBundle\Admin\CoreAdmin;
use Blast\Bundle\CoreBundle\DataSource\Iterator;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

trait Base
{
    /**
     * @var array
     */
    protected $exportFields = [];

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
        $actions = [];

        if ($this->isGranted('DELETE')) {
            $actions['delete'] = array(
               'label'              => 'action_delete',
               'translation_domain' => 'SonataAdminBundle',
               'ask_confirmation'   => true,
           );
        }

        return $this->handleBatchActions($actions);
    }

    /**
     * {@inheritdoc}
     **/
    public function getExportFormats()
    {
        $formats = [];
        foreach (parent::getExportFormats() as $format) {
            $formats[$format] = [];
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

        $request = $this->getConfigurationPool()->getContainer()->get('request_stack')->getMasterRequest();

        // nothing specific to add
        if (!($request->get('format')
            && isset($this->exportFields[$request->get('format')]))) {
            return parent::getExportFields();
        }

        // specificities for this format
        return $this->exportFields[$request->get('format')];
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function getDataSourceIterator()
    // {
    //     $datagrid = $this->getDatagrid();
    //     $datagrid->buildPager();
    //
    //     return $this->getModelManager()->getDataSourceIterator($datagrid, $this->getExportFields());
    //     // return new Iterator($datagrid->getQuery()->getQuery(), $this->getExportFields());
    // }
}
