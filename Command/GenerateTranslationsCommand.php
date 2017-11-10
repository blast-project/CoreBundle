<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Command;

use Blast\Bundle\CoreBundle\Generator\ArrayToYamlGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class GenerateAdminCommand.
 */
class GenerateTranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('blast:generate:translations')
            ->setDescription('Generates translation files from XLIFF to YAML')
            ->addArgument('bundle', InputArgument::REQUIRED, 'The bundle to generate translations for (ex: AcmeDemoBundle)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return class_exists('Sensio\\Bundle\\GeneratorBundle\\SensioGeneratorBundle');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $fs = new Filesystem();
        $finder = new Finder();
        $crawler = new Crawler();

        $path = $this->getContainer()->get('kernel')->locateResource('@' . $bundle);
        $transPath = $path . 'Resources/translations/';

        if (!$fs->exists($transPath)) {
            try {
                $fs->mkdir($transPath);
            } catch (IOExceptionInterface $e) {
                echo sprintf('An error occurred while creating your directory at %s', $e->getPath());
            }
        }

        foreach ($finder->files()->in($transPath) as $file) {
            $translations = [];

            $crawler->addXmlContent(file_get_contents($file->getPathName()));
            $crawler = $crawler->filter('trans-unit');

            foreach ($crawler as $transUnit) {
                $source = $transUnit
                            ->getElementsByTagName('source')
                            ->item(0)
                            ->nodeValue
                        ;
                $source = str_replace("'", "''", $source);

                $target = $transUnit
                            ->getElementsByTagName('target')
                            ->item(0)
                            ->nodeValue
                        ;
                $target = str_replace("'", "''", $target);

                $translations[$source] = $target;
            }

            $ymlGenerator = new ArrayToYamlGenerator($file, __DIR__ . '/../Resources/skeleton');
            $ymlGenerator->generate($translations, 'Messages.yml.twig');
        }

        return 0;
    }
}
