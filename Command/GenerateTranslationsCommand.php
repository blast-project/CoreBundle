<?php

/*
 * This file is part of the Blast package.
 *
 * (c) romain SANCHEZ <romain.sanchez@libre-informatique.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Blast\CoreBundle\Command;

use Blast\CoreBundle\Generator\ArrayToYamlGenerator;
use Blast\CoreBundle\Command\Traits\Interaction;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class GenerateAdminCommand.
 *
 */
class GenerateTranslationsCommand extends ContainerAwareCommand
{
    use Interaction;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('blast:generate:translations')
            ->setDescription('Generates translation files from XLIFF to YAML')
            ->addOption('bundle', 'b', InputOption::VALUE_OPTIONAL, 'the bundle to update the translations for (ex: AcmeAppBundle)')
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
        $bundle = $input->getOptions('bundle');
        $fs = new Filesystem();
        $finder = new Finder();
        $crawler = new Crawler();

        $path = $this->getContainer()->get('kernel')->locateResource('@' . $bundle);
        $transPath = $path . 'Resources/translations/';
        
        if ( !$fs->exists($transPath) )
            try
            {
                $fs->mkdir($transPath);
            } catch ( IOExceptionInterface $e )
            {
                echo sprintf('An error occurred while creating your directory at %s', $e->getPath());
            }
            
        foreach( $finder->files()->in($transPath) as $file )
        {
            $translations = [];

            $crawler->addXmlContent(file_get_contents($file->getPathName()));
            $crawler = $crawler->filter('trans-unit');

            foreach($crawler as $transUnit)
            {
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
            
            $ymlGenerator = new ArrayToYamlGenerator($file, __DIR__.'/../Resources/skeleton');
            $ymlGenerator->generate($translations, 'Messages.yml.twig');
        }
        
        return 0;
    }
    
    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $questionHelper->writeSection($output, 'Welcome to the Blast translations generator');
        
        if ( !$input->getOption('bundle') )
        {
            $bundle = $this->askAndValidate($input, $output, 'The bundle to generate translations for');

            $input->setOption('bundle', $bundle);
        }
    }

}
