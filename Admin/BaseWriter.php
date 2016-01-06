<?php

namespace Librinfo\CoreBundle\Admin;

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
        fwrite($this->file, ($this->position > 0 ? $separator : '').$data);
        ++$this->position;
    }
}
