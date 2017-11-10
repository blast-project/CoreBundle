<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Exporter;

use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\WriterInterface;
use Exporter\Handler as BaseHandler;

class Handler extends BaseHandler
{
    public function export()
    {
        $this->writer->open();

        foreach ($this->source as $key => $data) {
            $this->writer->write($data);
        }

        $this->writer->close();
    }

    /**
     * @param SourceIteratorInterface $source
     * @param WriterInterface         $writer
     *
     * @return Handler
     */
    public static function create(SourceIteratorInterface $source, WriterInterface $writer)
    {
        return new self($source, $writer);
    }
}
