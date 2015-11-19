<?php

namespace Librinfo\CoreBundle\Tools\Patches;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;
use Symfony\Component\Yaml\Yaml;

class Patcher extends ScriptHandler
{

    public static function applyPatches(CommandEvent $event)
    {

        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'applying custom patches on vendors');

        if (null === $consoleDir)
            return;

        static::executeCommand($event, $consoleDir, 'librinfo:patchs:apply', $options['process-timeout']);
    }
}