<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Exporter;

use Exporter\Source\SourceIteratorInterface;
use Exporter\Writer\TypedWriterInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exporter
{
    /**
     * @var TypedWriterInterface[]
     */
    private $writers;

    /**
     * @param TypedWriterInterface[] $writers an array of allowed typed writers, indexed by format name
     */
    public function __construct(array $writers = array())
    {
        $this->writers = array();

        foreach ($writers as $writer) {
            $this->addWriter($writer);
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @param string                  $format
     * @param string                  $filename
     * @param SourceIteratorInterface $source
     *
     * @return StreamedResponse
     */
    public function getResponse($format, $filename, SourceIteratorInterface $source)
    {
        if (!array_key_exists($format, $this->writers)) {
            throw new \RuntimeException(sprintf(
                'Invalid "%s" format, supported formats are : "%s"',
                $format,
                implode(', ', array_keys($this->writers))
            ));
        }
        $writer = $this->writers[$format];

        $callback = function () use ($source, $writer) {
            $handler = \Blast\Bundle\CoreBundle\Exporter\Handler::create($source, $writer);
            $handler->export();
        };

        $headers = array(
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        );

        $headers['Content-Type'] = $writer->getDefaultMimeType();

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Returns a simple array of export formats.
     *
     * @return string[] writer formats as returned by the TypedWriterInterface::getFormat() method
     */
    public function getAvailableFormats()
    {
        return array_keys($this->writers);
    }

    /**
     * The main benefit of this method is the type hinting.
     *
     * @param TypedWriterInterface $writer a possible writer for exporting data
     */
    public function addWriter(TypedWriterInterface $writer)
    {
        $this->writers[$writer->getFormat()] = $writer;
    }
}
