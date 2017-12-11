<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class PatcherApplyCommand extends ContainerAwareCommand
{
    use PatcherConfig,
        PatcherLogger;

    private $dryRun = true;

    /**
     * Command applies patch file [targetFilePath, patchPath].
     *
     * @var string
     */
    private $command = 'patch --no-backup-if-mismatch %3$s -f %1$s < %2$s > /dev/null';

    protected function configure()
    {
        $this
            ->setName('blast:patchs:apply')
            ->setDescription('Apply Patches from Librinfo on misc vendors')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force patch apply');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();

        foreach ($this->config['patches'] as $patch) {
            if ($patch['enabled'] === true && ($patch['patched'] == false || $input->getOption('force') === true)) {
                $this->applyPatch($patch['targetFile'], $patch['patchFile'], $patch['id']);
            }
        }

        $this->displayMessages($output);
    }

    private function applyPatch($targetFile, $patchFile, $patchId)
    {
        $targetFile = $this->config['paths']['projectDir'] . '/' . $targetFile;
        $patchFile = $this->config['paths']['patchFilesDir'] . '/' . $patchFile;

        if (!file_exists($targetFile) || !file_exists($patchFile)) {
            $this->error('Missing patches :');
            if (!file_exists($targetFile)) {
                $this->comment(' - ' . $targetFile);
            }
            if (!file_exists($patchFile)) {
                $this->comment(' - ' . $patchFile);
            }

            return;
        }

        $command = $this->getCommand($targetFile, $patchFile);
        $out = null;
        system($command, $out);

        if ($out == 0) {
            $this->dryRun = false;
            $command = $this->getCommand($targetFile, $patchFile);
            system($command, $out);
            $this->dryRun = true;

            if ($out != 0) {
                $this->error('The patch ' . $patchFile . ' cannot be applyed on file ' . $targetFile);

                return;
            }

            foreach ($this->config['patches'] as $key => $patch) {
                if ($patch['id'] == $patchId) {
                    $this->config['patches'][$key]['patched'] = true;
                }
            }

            file_put_contents(
                $this->config['paths']['configFile'],
                Yaml::dump(
                    ['patches' => $this->config['patches']]
                )
            );
        } else {
            $this->comment('The patch ' . $patchFile . ' cannot be applyed on file ' . $targetFile);
        }
    }

    private function getCommand($targetFile, $patchFile)
    {
        return sprintf(
            $this->command,
            $targetFile,
            $patchFile,
            $this->dryRun ? '--dry-run' : ''
        );
    }
}
