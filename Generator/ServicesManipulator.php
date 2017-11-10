<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Generator;

use Sonata\AdminBundle\Manipulator\ServicesManipulator as BaseManipulator;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ServicesManipulator.
 */
class ServicesManipulator extends BaseManipulator
{
    /**
     * @var string
     */
    private $template = '    %s:
        class: %s
        arguments: [~, %s, %s]
        tags:
            - name: sonata.admin
              manager_type: orm
              group: admin
              label: %s

';

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = (string) $file;
    }

    /**
     * @param string $serviceId
     * @param string $modelClass
     * @param string $adminClass
     * @param string $controllerName
     * @param string $managerType
     *
     * @throws \RuntimeException
     */
    public function addResource($serviceId, $modelClass, $adminClass, $controllerName, $managerType)
    {
        $rc = new \ReflectionClass($modelClass);
        $modelClassShortName = $rc->getShortName();

        $code = "services:\n";

        if (is_file($this->file)) {
            $code = rtrim(file_get_contents($this->file));
            $data = (array) Yaml::parse($code);

            if ($code !== '') {
                $code .= "\n";
            }

            if (array_key_exists('services', $data)) {
                if (array_key_exists($serviceId, (array) $data['services'])) {
                    throw new \RuntimeException(sprintf(
                        'The service "%s" is already defined in the file "%s".',
                        $serviceId,
                        realpath($this->file)
                    ));
                }

                if ($data['services'] !== null) {
                    $code .= "\n";
                }
            } else {
                $code .= $code === '' ? '' : "\n" . "services:\n";
            }
        }

        $code .= sprintf(
            $this->template,
            $serviceId,
            $adminClass,
            $modelClass,
            $controllerName,
            $modelClassShortName,
            $managerType,
            current(array_slice(explode('\\', $modelClass), -1))
        );
        @mkdir(dirname($this->file), 0777, true);

        if (@file_put_contents($this->file, $code) === false) {
            throw new \RuntimeException(sprintf(
                'Unable to append service "%s" to the file "%s". You will have to do it manually.',
                $serviceId,
                $this->file
            ));
        }
    }
}
