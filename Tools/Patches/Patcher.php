<?php

namespace Librinfo\CoreBundle\Tools\Patches;

use Composer\Script\Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

class Patcher extends ScriptHandler
{

    public static function applyPatches(Event $event)
    {

        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'applying custom patches on vendors');

        if (null === $consoleDir)
            return;

        static::executeCommand($event, $consoleDir, 'librinfo:patchs:apply', $options['process-timeout']);
    }
}