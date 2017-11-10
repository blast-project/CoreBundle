<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType as SymfonyAbstractType;

class AbstractType extends SymfonyAbstractType
{
    /**
     * {@inheritdoc}
     *
     * @todo Remove when dropping Symfony <2.8 support
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * getBlockPrefix.
     *
     * When $this is a AppBundle\Form\Type\MyType, it returns app_my
     * When $this is a Sil\Bundle\AppBundle\Form\Type\MyType, it returns blast_app_my
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        $rc = new \ReflectionClass($this);

        // Non-greedy ("+?") to match "type" suffix, if present
        $fqcn = preg_replace(
            array('/([^\\\\])(Bundle)?\\\\Form\\\\Type(\\\\[^\\\\]+?)(Type)?$/i', '/\\\\/'),
            array('\\1\\3', '_'),
            $rc->getName()
        );

        return strtolower($fqcn);
    }
}
