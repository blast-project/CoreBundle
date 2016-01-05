<?php

namespace Librinfo\CoreBundle\Admin;

use Exporter\Source\SourceIteratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Export\Exporter as BaseExporter;
use Librinfo\CRMBundle\Entity\Circle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class Exporter extends BaseExporter
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
    protected $router;
    
    public function getResponse($format, $filename, SourceIteratorInterface $source)
    {
        return parent::getResponse($format, $filename, $source);
    }

    /**
     * setTokenStorage
     *
     * @param $tokenStorage     TokenStorageInterface
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }
    
    /**
     * setTranslator
     *
     * @param $translator     TokenStorageInterface
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        return $this;
    }
    
    /**
     * setTwig
     *
     * @param $twig     \Twig_Environment
     */
    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
        return $this;
    }
    
    /**
     * setRouter
     *
     * @param $router     \Twig_Environment
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }
}
