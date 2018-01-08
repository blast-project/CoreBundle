<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Datagrid\ListMapper;

trait Actions
{
    protected function addActions()
    {
        $actionKey = '_actions';
        $mapperClass = ListMapper::class;
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
            if (!isset($blast[$class][ListMapper::class])) {
                continue;
            }

            $listFields = $this->getListFieldDescriptions();
            if (!isset($listFields['_action'])) {
                continue;
            }

            $config = $blast[$class][ListMapper::class];
            if (isset($blast['all'][ListMapper::class])) {
                $config = array_merge_recursive(
                    $config,
                    $blast['all'][ListMapper::class]
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
