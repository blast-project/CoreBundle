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

$header = <<<EOF
This file is part of the Blast Project package.

Copyright (C) 2015-2017 Libre Informatique

This file is licenced under the GNU LGPL v3.
For the full copyright and license information, please view the LICENSE.md
file that was distributed with this source code.
EOF;

// PHP-CS-Fixer 1.x
if (class_exists('Symfony\CS\Fixer\Contrib\HeaderCommentFixer')) {
    \Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);
}

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
;

$config = PhpCsFixer\Config::create()
    ->setRules(array(
        '@Symfony'               => true,
        'binary_operator_spaces' => ['align_double_arrow' => true],
        'concat_space'           => ['spacing'=>'one'],
        'yoda_style'             => null,
    ))
    ->setFinder($finder);

// PHP-CS-Fixer 2.x
if (method_exists($config, 'setRules')) {
    $config->setRules(array_merge($config->getRules(), array(
        'header_comment' => array('header' => $header),
    )));
}

return $config;
