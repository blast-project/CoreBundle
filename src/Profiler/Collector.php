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

namespace Blast\CoreBundle\Profiler;

use Doctrine\Common\Collections\ArrayCollection;

class Collector
{
    /**
     * @var mixed
     */
    private $data;

    public function __construct()
    {
        // $this->data = [];
    }

    /**
     * @param mixed $data
     */
    public function collect($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
