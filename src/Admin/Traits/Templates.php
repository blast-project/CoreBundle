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

namespace Blast\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

trait Templates
{
    /**
     * @param type $mapper
     *
     * @return type
     */
    protected function fixTemplates($mapper)
    {
        $librinfo = $this->getConfigurationPool()->getContainer()->getParameter('blast');
        if (!isset($librinfo['configuration']) && isset($librinfo['configuration']['templates'])) {
            return $this;
        }

        $mapping = [
            'ShowMapper' => 'show',
            'ListMapper' => 'list',
        ];
        $rc = new \ReflectionClass($mapper);
        if (!isset($mapping[$rc->getShortName()])) {
            return $this;
        }

        $mapType = $mapping[$rc->getShortName()];
        if (!isset($librinfo['configuration']['templates'][$mapType])) {
            return $this;
        }

        // get back the new templates
        $templates = $librinfo['configuration']['templates'][$mapType];

        // checks if something has to be done
        foreach ($this->{$mapType.'FieldDescriptions'} as $fd) {
            if (isset($templates[$fd->getType()])) {
                $fd->setTemplate($templates[$fd->getType()]);
            }
        }

        return $this;
    }
}
