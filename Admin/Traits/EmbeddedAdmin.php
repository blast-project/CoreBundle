<?php

/*
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin\Traits;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Blast\Bundle\CoreBundle\Admin\CoreAdmin;

trait EmbeddedAdmin
{
    use Base;

    /**
     * @param FormMapper $mapper
     */
    public function configureFormFields(FormMapper $mapper)
    {
        CoreAdmin::configureFormFields($mapper);
        if ($this->getParentFieldDescription()) {
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
        }
    }

    /**
     * @param FormMapper $mapper
     */
    public function configureShowFields(ShowMapper $mapper)
    {
        CoreAdmin::configureShowFields($mapper);
        if ($this->getParentFieldDescription()) {
            $mapper->remove($this->getParentFieldDescription()->getAssociationMapping()['mappedBy']);
        }
    }
}
