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

    protected $namePrefix; /* may be the bundle name */
    protected $nameFix; /* may be the admin name */
    
    public function __construct($prefix = 'Blast\CoreBundle', $fix = 'CoreAdmin')
    {
        $this->setPrefix($prefix);
        $this->setFix($fix);
    }
    
    public function setPrefix($prefix): void
    {
        $this->namePrefix = $this->cleanStr($prefix);
    }
    
    public function setFix($fix): void
    {
        /* Warning last set is current ... */
        $this->nameFix = $this->cleanStr($fix);
    }
    
    public function cleanStr($str) : string
    {
        $str = strtolower(str_replace('\\', '.', $str));
        /* user love \ */
        $str = str_replace('..', '.', $str);
        return $str;
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
