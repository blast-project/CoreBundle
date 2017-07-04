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
    /**
     * {@inheritdoc}
     */
    public function getLabel($label, $context = '', $type = '')
    {
        $label = str_replace('.', '_', $label);

        return sprintf('%s.%s.%s', 'librinfo', $type, strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $label)));
    }
}
