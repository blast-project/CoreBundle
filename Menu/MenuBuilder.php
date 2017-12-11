<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU LGPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Menu;

use Knp\Menu\MenuItem;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class MenuBuilder
{
    private $factory;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * @var array
     */
    private $rootMenu;

    /**
     * @var array
     */
    private $settingsMenu;

    /**
     * @var MenuItem
     */
    private $root;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    private function getRoot()
    {
        if ($this->root === null) {
            $this->root = $this->factory->createItem('sil_menu_root');
        }

        return $this->root;
    }

    public function createGlobalSidebarMenu(RequestStack $requestStack)
    {
        $root = $this->getRoot();

        $this->iterateMenu($this->rootMenu, $root);

        $menu = $root->addChild('blast.menu_label.application_settings');
        $menu->setExtra('icon', '<i class="fa fa-sliders"></i>');

        $this->iterateMenu($this->settingsMenu, $menu);

        // Stock settings
        /*
        $submenu = $menu->addChild('sil.stock.menu_label.stock_management_settings');
        $submenu->addChild('sil.stock.menu_label.warehouses', ['route' => 'admin_stock_warehouse_list']);
        $submenu->addChild('sil.stock.menu_label.locations', ['route' => 'admin_stock_location_list']);
        $submenu->addChild('sil.stock.menu_label.uom_types', ['route' => 'admin_stock_uomtype_list']);
        $submenu->addChild('sil.stock.menu_label.uoms', ['route' => 'admin_stock_uom_list']);
        */

        return $root;
    }

    private function iterateMenu($items, $menuNode)
    {
        // Sorting current menu items
        uasort($items, function ($item1, $item2) {
            return $item1['order'] <=> $item2['order'];
        });

        foreach ($items as $label => $item) {
            $displayItem = true;
            $route = isset($item['route'])
                ? ['route' => $item['route']]
                : []
            ;

            // Handling explicit hide of item
            if (isset($item['display'])) {
                $displayItem = (bool) $item['display'];
            }

            // Handling role(s) access of item
            if (isset($item['roles'])) {
                $displayItem = false;
                foreach ($item['roles'] as $role) {
                    if ($this->authorizationChecker->isGranted($role, $this->tokenStorage->getToken()->getUser())) {
                        $displayItem = true;
                    }
                }
            }

            if ($displayItem) {
                $menuNodeCurrent = $menuNode->addChild($label, $route);

                $menuNodeCurrent
                    ->setExtra(
                        'icon',
                        isset($item['icon'])
                            ? '<i class="fa fa-' . $item['icon'] . '"></i>'
                            : ''
                    );

                if (isset($item['children']) && count($item['children']) > 0) {
                    // Recursive call
                    $this->iterateMenu($item['children'], $menuNodeCurrent);
                } else {
                    $menuNodeCurrent->setExtra('on_top', true);
                }
            }
        }
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage(TokenStorage $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param AuthorizationChecker $authorizationChecker
     */
    public function setAuthorizationChecker(AuthorizationChecker $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param array $menu
     */
    public function setRootMenu($menu): void
    {
        $this->rootMenu = $menu;
    }

    /**
     * @param array $menu
     */
    public function setSettingsMenu($menu): void
    {
        $this->settingsMenu = $menu;
    }
}
