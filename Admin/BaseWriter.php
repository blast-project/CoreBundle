<?php

/*
 * This file is part of the Sil Project.
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Admin;

use Exporter\Writer\WriterInterface;

abstract class BaseWriter implements WriterInterface
{
    protected $filename;
    protected $file;
    protected $position;

    public function configure($filename)
    {
        $this->filename = $filename;
        $this->position = 0;

        if (is_file($filename)) {
            throw new \RuntimeException(sprintf('The file %s already exist', $filename));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $this->file = fopen($this->filename, 'w', false);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        fclose($this->file);
    }

    protected function _write($data, $separator = "\n")
    {
        fwrite($this->file, ($this->position > 0 ? $separator : '') . $data);
        ++$this->position;
    }
}
