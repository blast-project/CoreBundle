<?php

/*
 *
 * Copyright (C) 2015-2017 Libre Informatique
 *
 * This file is licenced under the GNU GPL v3.
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Blast\Bundle\CoreBundle\Controller;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CoreController as BaseCoreController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Extension of Sonata CoreController.
 *
 * @author Marcos Bezerra de Menezes <marcos.bezerra@libre-informatique.fr>
 */
class CoreController extends BaseCoreController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse|Response
     *
     * @throws \RuntimeException
     */
    public function searchAction(Request $request)
    {
        if ($request->get('admin') && $request->isXmlHttpRequest()) {
            try {
                $admin = $this->getAdminPool()->getAdminByAdminCode($request->get('admin'));
            } catch (ServiceNotFoundException $e) {
                throw new \RuntimeException('Unable to find the Admin instance', $e->getCode(), $e);
            }

            if (!$admin instanceof AdminInterface) {
                throw new \RuntimeException('The requested service is not an Admin instance');
            }

            $handler = $this->getSearchHandler();

            $results = array();

            if ($pager = $handler->search($admin, $request->get('q'), $request->get('page'), $request->get('offset'))) {
                foreach ($pager->getResults() as $result) {
                    $results[] = array(
                        'label' => $admin->toString($result),
                        'link'  => $admin->generateObjectUrl('show', $result),  // Sonata uses "edit", we prefer "show"
                        'id'    => $admin->id($result),
                    );
                }
            }

            $response = new JsonResponse(array(
                'results' => $results,
                'page'    => $pager ? (int) $pager->getPage() : false,
                'total'   => $pager ? (int) $pager->getNbResults() : false,
            ));
            $response->setPrivate();

            return $response;
        }

        return $this->render($this->container->get('sonata.admin.pool')->getTemplate('search'), array(
            'base_template'       => $this->getBaseTemplate(),
            'breadcrumbs_builder' => $this->get('sonata.admin.breadcrumbs_builder'),
            'admin_pool'          => $this->container->get('sonata.admin.pool'),
            'query'               => $request->get('q'),
            'groups'              => $this->getAdminPool()->getDashboardGroups(),
        ));
    }
}
