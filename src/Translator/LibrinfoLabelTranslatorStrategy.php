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

namespace Blast\CoreBundle\Translator;

use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

/**
 * Class LibrinfoLabelTranslatorStrategy.
 *
 * Provides a specific label translation strategy for Librinfo.
 * It is based on UnderscoreLabelTranslatorStrategy, but without the context,
 * and labels are prefixed by "librinfo.label."
 *
 * i.e. isValid => librinfo.label.is_valid
 */
class LibrinfoLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    protected $namePrefix;

    public function __construct($prefix = 'Blast\CoreBundle')
    {
        $this->setPrefix($prefix);
    }

    public function setPrefix($prefix, $append = false) : void
    {
        $prefix = strtolower(str_replace('\\', '.', $prefix));
        $this->namePrefix = ($append) ? $this->namePrefix . '.' . $prefix : $prefix;

        /* user love \ */
        $this->namePrefix = str_replace('..', '.', $this->namePrefix);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel($label, $context = '', $type = ''): string
    {
        $label = str_replace('.', '_', $label);

        $label = sprintf('%s', strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));

        return $this->namePrefix . '.' . $label;
    }
}
