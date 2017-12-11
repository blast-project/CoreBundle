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

use Symfony\Component\Yaml\Yaml;

trait PatcherConfig
{
    private $config;

    private function loadConfig()
    {
        $configPath = $this->getContainer()->get('kernel')->locateResource('@BlastCoreBundle/Tools/Patches/patches.yml');
        $baseDir = str_replace('/patches.yml', '', $configPath);

        $this->config = Yaml::parse(
            file_get_contents($configPath)
        );

        if ($this->config['patches'] == null) {
            $this->config['patches'] = [];
        }

        $this->config['paths'] = [
            'projectDir'    => $this->getContainer()->getParameter('kernel.project_dir'),
            'patchFilesDir' => $baseDir . '/patches',
            'configFile'    => $configPath,
        ];
    }
}
