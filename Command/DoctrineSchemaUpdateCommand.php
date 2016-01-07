<?php

namespace Librinfo\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Tools\SchemaTool;
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

    /**
     * {@inheritdoc}
     */
    protected function executeSchemaCommand(InputInterface $input, OutputInterface $output, SchemaTool $schemaTool, array $metadatas)
    {
        $updated_metadatas = $this->addSearchableEntities($metadatas);

        return parent::executeSchemaCommand($input, $output, $schemaTool, $updated_metadatas);
    }


    protected function addSearchableEntities(array $metadatas)
    {
        // look for SearchIndexEntity in $metadatas, clone the metadata and remove it from $metadatas
        $searchMetadata = null;
        foreach ( $metadatas as $k => $metadata )
            if ( $metadata->name == 'Librinfo\CoreBundle\Entity\SearchIndexEntity' )
            {
                $searchMetadata = clone $metadata;
                break;
            }
        if ( !$searchMetadata )
            return $metadatas;

        unset($metadatas[$k]);

        // create a SearchIndexEntity metadata for each entity that have the Searchable trait
        foreach ( $metadatas as $metadata )
        {
            $traits = $metadata->reflClass->getTraitNames();
            if ( !in_array('Librinfo\CoreBundle\Entity\Traits\Searchable', $traits) )
                continue;

            $newMetadata = clone $searchMetadata;
            $newMetadata->setTableName($metadata->getTableName() . '_searchindex');
            $metadatas[] = $newMetadata;
        }

        return $metadatas;
    }

}
