<?php

namespace Blast\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PatcherListCommand extends ContainerAwareCommand
{

    use PatcherConfig,
        PatcherLogger;

    protected function configure()
    {
        $this
            ->setName('librinfo:patchs:list')
            ->setDescription('List Patches from Librinfo on misc vendors');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();

        $this->info("\nListing available patches:\n\n");
        $this->info('  - - - -  ');
        foreach ($this->config['patches'] as $patch)
        {
            $this->info('id: ', false);
            $this->comment($patch['id']);
            $this->info('enabled: ', false);
            $this->comment($patch['enabled'] ? 'true':'false');
            $this->info('patched: ', false);
            $this->comment($patch['patched'] ? 'true':'false');
            $this->info('targetFile: ', false);
            $this->comment($patch['targetFile']);
            $this->info('patchFile: ', false);
            $this->comment($patch['patchFile']);
            $this->info('  - - - -  ');
        }

        $this->displayMessages($output);
    }

}