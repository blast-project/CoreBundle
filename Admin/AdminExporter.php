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

namespace Blast\Bundle\CoreBundle\Admin;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Blast\Bundle\CoreBundle\Exporter\Exporter;
use Sonata\AdminBundle\Admin\AdminInterface;

class AdminExporter
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Router
     */
    protected $router; /**
     * @var Exporter service from the exporter bundle
     */
    private $exporter;

    /**
     * @param Exporter will be used to get global settings
     */
    public function __construct(Exporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * Queries an admin for its default export formats, and falls back on global settings.
     *
     * @param AdminInterface $admin the current admin object
     *
     * @return string[] an array of formats
     */
    public function getAvailableFormats(AdminInterface $admin)
    {
        $adminExportFormats = $admin->getExportFormats();

        // NEXT_MAJOR : compare with null
        if ($adminExportFormats != array('json', 'xml', 'csv', 'xls')) {
            return $adminExportFormats;
        }

        return $this->exporter->getAvailableFormats();
    }

    /**
     * Builds an export filename from the class associated with the provided admin,
     * the current date, and the provided format.
     *
     * @param AdminInterface $admin  the current admin object
     * @param string         $format the format of the export file
     */
    public function getExportFilename(AdminInterface $admin, $format)
    {
        $class = $admin->getClass();

        return sprintf(
            'export_%s_%s.%s',
            strtolower(substr($class, strripos($class, '\\') + 1)),
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );
    }

    /**
     * setTokenStorage.
     *
     * @param $tokenStorage     TokenStorageInterface
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    /**
     * setTranslator.
     *
     * @param $translator     TokenStorageInterface
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * setTwig.
     *
     * @param $twig     \Twig_Environment
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * setRouter.
     *
     * @param $router     Router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;

        return $this;
    }
}
