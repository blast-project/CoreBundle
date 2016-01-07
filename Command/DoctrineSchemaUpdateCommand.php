<?php

namespace Librinfo\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\UpdateSchemaDoctrineCommand;

class DoctrineSchemaUpdateCommand extends UpdateSchemaDoctrineCommand
{

    // TODO: use a different name (librinfo:schema:update) ??

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump('librinfo');

        return parent::execute($input, $output);
    }

}
