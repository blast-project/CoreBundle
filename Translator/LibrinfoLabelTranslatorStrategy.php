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

namespace Blast\Bundle\CoreBundle\Translator;

use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;

/**
 * Class LibrinfoLabelTranslatorStrategy.
 *
 * Provides a specific label translation strategy for Librinfo.
 * It is based on UnderscoreLabelTranslatorStrategy, but without the context,
 * and labels are prefixed by "blast.label."
 *
 * i.e. isValid => blast.label.is_valid
 *
 * Type to find missing translation : bin/console blast:translations:extract -f YAML
 */
class LibrinfoLabelTranslatorStrategy implements LabelTranslatorStrategyInterface
{
    protected $namePrefix; /* may be the bundle name */
    protected $nameFix; /* may be the admin name */
    protected $nameFixSave; /* may be the admin name */

    public function __construct($prefix = 'Blast\Bundle\CoreBundle', $fix = 'CoreAdmin')
    {
        $this->setPrefix($prefix);
        $this->setFix($fix);
    }

    public function setPrefix($prefix): LibrinfoLabelTranslatorStrategy
    {
        $this->namePrefix = $this->cleanStr($prefix);

        return $this;
    }

    public function setFix($fix, $isTmp = false): LibrinfoLabelTranslatorStrategy
    {
        if ($isTmp) {
            $this->nameFixSave = $this->nameFix;
        }

        /* Warning last set is current ... */
        $this->nameFix = $this->cleanStr($fix);

        return $this;
    }

    public function cleanStr($str): string
    {
        $str = strtolower(str_replace('\\', '.', $str));
        /* user love \ */
        $str = str_replace('..', '.', $str);
        $str = str_replace(' ', '_', $str);

        return $str;
    }

    public function doResetFix()
    {
        if (isset($this->nameFixSave)) {
            $this->nameFix = $this->nameFixSave;
            $this->nameFixSave = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($label, $context = '', $type = ''): string
    {
        $label = str_replace('.', '_', $label);
        $label = $this->cleanStr($label); /* if there is still some \ */
        $label = sprintf('%s', strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));

        //$resLabel = $this->namePrefix . '.' . $this->nameFix . '.' . $context . '.' . $label;
        //$this->doResetFix(); /* for $isTmp to true see setFix */
        $resLabel = $this->namePrefix . '.' . $label;

        $resLabel = $this->cleanStr($resLabel);

        return $resLabel;
    }
}
