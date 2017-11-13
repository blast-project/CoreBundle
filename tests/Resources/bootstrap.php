<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

// fix encoding issue while running text on different host with different locale configuration
setlocale(LC_ALL, 'en_US.UTF-8');
if (file_exists($file = __DIR__ . '/autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__ . '/autoload.php.dist')) {
    require_once $file;
}

// try to get Symfony's PHPunit Bridge
$files = array_filter(array(
    __DIR__ . '/../../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php',
    __DIR__ . '/../../vendor/symfony/phpunit-bridge/bootstrap.php',
), 'file_exists');

if ($files) {
    require_once current($files);
}

// try to get outer extension fake file
if (file_exists($file = __DIR__ . '/autoload_outer_extension.php')) {
    require_once $file;
}
