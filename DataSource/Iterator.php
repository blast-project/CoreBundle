<?php

namespace Librinfo\CoreBundle\DataSource;

use Exporter\Source\DoctrineORMQuerySourceIterator;

class Iterator extends DoctrineORMQuerySourceIterator
{
    public function getQuery()
    {
        return $this->query;
    }
}
