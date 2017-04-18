<?php

namespace Blast\CoreBundle\Admin\Traits;

trait Actions
{
    protected function addActions()
    {
        $actionKey = '_actions';
        $mapperClass = 'Sonata\AdminBundle\Datagrid\ListMapper';
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            if (isset($blast[$class][$mapperClass])) {
                $config = $blast[$class][$mapperClass];

                if (isset($blast['all'][$mapperClass])) {
                    $config = array_merge_recursive(
                    $config,
                    $blast['all'][$mapperClass]
                );
                }

                if (isset($config['add'][$actionKey])) {
                    $listFields = $this->getListFieldDescriptions();

                    if (isset($listFields['_action'])) {
                        $conf = $listFields['_action'];
                        $options = $conf->getOptions();
                        $actions = $options['actions'];

                        foreach ($config['add'][$actionKey] as $key => $action) {
                            $actions[$key] = $action;
                        }

                        $options['actions'] = $actions;
                        $conf->setOptions($options);
                        $listFields['_action'] = $conf;
                    }
                }
            }
        }
    }

    protected function removeActions()
    {
        $blast = $this->getConfigurationPool()->getContainer()->getParameter('blast');

        foreach ($this->getCurrentComposition() as $class) {
            if (!isset($blast[$class]['Sonata\AdminBundle\Datagrid\ListMapper'])) {
                continue;
            }

            $listFields = $this->getListFieldDescriptions();
            if (!isset($listFields['_action'])) {
                continue;
            }

            $config = $blast[$class]['Sonata\AdminBundle\Datagrid\ListMapper'];
            if (isset($blast['all']['Sonata\AdminBundle\Datagrid\ListMapper'])) {
                $config = array_merge_recursive(
                    $config,
                    $blast['all']['Sonata\AdminBundle\Datagrid\ListMapper']
                );
            }
            if (!isset($config['remove']['_actions'])) {
                continue;
            }

            $conf = $listFields['_action'];
            $options = $conf->getOptions();
            $actions = $options['actions'];

            foreach ($config['remove']['_actions'] as $action) {
                if (isset($actions[$action])) {
                    unset($actions[$action]);
                }
            }

            $options['actions'] = $actions;
            $conf->setOptions($options);
            $listFields['_action'] = $conf;
        }
    }
}
