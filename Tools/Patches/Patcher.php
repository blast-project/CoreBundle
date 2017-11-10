<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Tools\Patches;

use Composer\Script\Event;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler;

class Patcher extends ScriptHandler
{
    public static function applyPatches(Event $event)
    {
        $options = static::getOptions($event);
        $consoleDir = static::getConsoleDir($event, 'applying custom patches on vendors');

        if (null === $consoleDir) {
            return;
        }

        static::executeCommand($event, $consoleDir, 'blast:patchs:apply --force', $options['process-timeout']);
    }
}
