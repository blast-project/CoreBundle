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
        foreach ($this->config['patches'] as $patch) {
            $this->info('id: ', false);
            $this->comment($patch['id']);
            $this->info('enabled: ', false);
            $this->comment($patch['enabled'] ? 'true' : 'false');
            $this->info('patched: ', false);
            $this->comment($patch['patched'] ? 'true' : 'false');
            $this->info('targetFile: ', false);
            $this->comment($patch['targetFile']);
            $this->info('patchFile: ', false);
            $this->comment($patch['patchFile']);
            $this->info('  - - - -  ');
        }

        $this->displayMessages($output);
    }
}
