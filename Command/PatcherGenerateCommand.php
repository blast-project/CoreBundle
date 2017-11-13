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

use DateTime;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class PatcherGenerateCommand extends ContainerAwareCommand
{
    /**
     * Command generates patch file [originalFilePath, modifiedPathFile, outputFilename].
     *
     * @var string
     */
    private $command = 'diff -Naur %1$s %2$s > %3$s';

    /**
     * @var DateTime
     */
    private $now;

    use PatcherConfig,
        PatcherLogger;

    protected function configure()
    {
        $this
            ->setName('librinfo:patchs:generate')
            ->setDescription('Generate Patches from Librinfo on misc vendors')
            ->addArgument(
                'original-file',
                InputArgument::REQUIRED,
                'The (absolute or project root relative) path to original file'
            )
            ->addArgument(
                'modified-file',
                InputArgument::REQUIRED,
                'The (absolute or project root relative) path to modified file'
            )->addArgument(
                'target-file',
                InputArgument::REQUIRED,
                'The project root relative path to target file (the file to be patched)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->now = new DateTime('NOW');
        $this->loadConfig();

        $originalPath = $input->getArgument('original-file');
        $modifiedPath = $input->getArgument('modified-file');
        $targetPath = $input->getArgument('target-file');

        if (substr($targetPath, 0, 1) == '/') {
            $this->error('The target-file argument must be a project root relative path');
            $this->comment($targetPath);

            return;
        }

        $this->createPatch($originalPath, $modifiedPath, $targetPath);

        $this->displayMessages($output);
    }

    private function createPatch($originalPath, $modifiedPath, $targetPath)
    {
        $this->info('Generating patches');

        $modifiedPath = $this->managePath($modifiedPath, 'patched');
        $originalPath = $this->managePath($originalPath, 'original');

        if (!file_exists($originalPath) || !file_exists($modifiedPath)) {
            $this->error('Files not found :');
            if (!file_exists($originalPath)) {
                $this->comment(' - ' . $originalPath);
            }
            if (!file_exists($modifiedPath)) {
                $this->comment(' - ' . $modifiedPath);
            }

            return;
        }

        $command = sprintf(
            $this->command,
            $originalPath,
            $modifiedPath,
            $this->config['paths']['patchFilesDir'] . '/' . $this->now->getTimestamp() . '.txt'
        );

        $this->info('Executing command : ');
        $this->comment($command);
        system($command);

        $this->addPatchToConfigFile(
            $targetPath,
            $this->config['paths']['patchFilesDir'] . '/' . $this->now->getTimestamp() . '.txt'
        );
    }

    private function managePath($path, $type)
    {
        if (substr($path, 0, 1) !== '/' && !filter_var($path, FILTER_VALIDATE_URL)) {
            $path = $this->config['paths']['rootDir'] . '/' . $path;
        } elseif (filter_var($path, FILTER_VALIDATE_URL)) {
            if (copy($path, $this->config['paths']['patchFilesDir'] . "/$type/" . $this->now->getTimestamp())) {
                $path = $this->config['paths']['patchFilesDir'] . "/$type/" . $this->now->getTimestamp();
            }
        }

        return $path;
    }

    private function addPatchToConfigFile($targetPath, $patchFile)
    {
        $conf = [
            'patches' => [
                [
                    'id'         => $this->now->getTimestamp(),
                    'enabled'    => true,
                    'patched'    => false,
                    'targetFile' => $targetPath,
                    'patchFile'  => str_replace($this->config['paths']['rootDir'] . '/', '', $patchFile),
                ],
            ],
        ];

        $fullConf = array_merge($conf['patches'], $this->config['patches']);
        $yamlConfig = Yaml::dump(['patches' => $fullConf]);

        file_put_contents($this->config['paths']['configFile'], $yamlConfig);
    }
}
