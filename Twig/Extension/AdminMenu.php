<?php

namespace Librinfo\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_Environment;
use Twig_Extension;

class AdminMenu extends Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Array
     */
    private $menuConfiguration;

    /**
     * AdminMenu constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->getMenuConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'showAdminMenu' => new \Twig_Function_Method(
                $this,
                'showAdminMenu',
                array(
                    'is_safe'           => array('html'),
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * @param Twig_Environment $twig
     * @param null             $position
     *
     * @return string
     * @internal param array $string
     *
     */
    public function showAdminMenu(Twig_Environment $twig, $position = null)
    {
        foreach ($this->menuConfiguration as $menu)
        if ($position !== null && $position == $menu['position'])
            return $twig->render('LibrinfoDecoratorBundle:Admin/Menu:menu.html.twig', array(
                'menu' => $menu['elements']
            ));

        return null;

    }

    private function getMenuConfiguration()
    {
        $config = $this->container->getParameter('librinfo-core');

        if (isset($config['custom_menu']))
            $this->menuConfiguration = $config['custom_menu'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'AdminMenu';
    }
}