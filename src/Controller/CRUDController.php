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

namespace Blast\CoreBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as SonataController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CRUDController.
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CRUDController extends SonataController
{
    /**
     * The related Admin class.
     *
     * @var AdminInterface
     */
    protected $admin;

    /**
     * Duplicate action.
     *
     * @return response
     */
    public function duplicateAction()
    {
        $id = $this->getRequest()->get($this->admin->getIdParameter());
        $object = clone $this->admin->getObject($id);

        $preResponse = $this->preDuplicate($object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        return $this->createAction($object);
    }

    /**
     * Create action.
     *
     * @param object $object
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function createAction($object = null)
    {
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $this->admin->checkAccess('create');

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->render(
                'SonataAdminBundle:CRUD:select_subclass.html.twig',
                array(
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ),
                null,
                $request
            );
        }

        $object = $object ? $object : $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                $this->admin->checkAccess('create', $object);

                try {
                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->admin->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->admin->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form' => $view,
            'object' => $object,
        ), null);
    }

    /**
     * Generate Entity Code action.
     *
     * @param int|string|null $id
     *
     * @return JsonResponse
     */
    public function generateEntityCodeAction($id = null)
    {
        $request = $this->getRequest();

        $id = $request->get($this->admin->getIdParameter());
        if ($id) {
            $subject = $this->admin->getObject($id);
            if (!$subject) {
                $error = sprintf('unable to find the object with id : %s', $id);

                return new JsonResponse(['error' => $error]);
            }
            try {
                $this->admin->checkAccess('edit', $subject); // TODO: is it necessary ? (we are not editing the entity)
            } catch (Exception $exc) {
                $error = $exc->getMessage();

                return new JsonResponse(['error' => $error]);
            }
        } else {
            $subject = $this->admin->getNewInstance();
        }

        $this->admin->setSubject($subject);

        $form = $this->admin->getForm();
        $form->setData($subject);
        $form->submit($request->request->get($form->getName()));
        $entity = $form->getData();

        $field = $request->query->get('field', 'code');
        $registry = $this->get('blast_core.code_generators');
        $generator = $registry::getCodeGenerator(get_class($entity), $field);
        try {
            $code = $generator::generate($entity);

            return new JsonResponse(['code' => $code]);
        } catch (\Exception $exc) {
            $error = $this->get('translator')->trans($exc->getMessage());

            return new JsonResponse(['error' => $error, 'generator' => get_class($generator)]);
        }
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from createAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preCreate(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from editAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preEdit(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from deleteAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preDelete(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from showAction.
     *
     * @param Request $request
     * @param mixed   $object
     *
     * @return Response|null
     */
    protected function preShow(Request $request, $object)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from listAction.
     *
     * @param Request $request
     *
     * @return Response|null
     */
    protected function preList(Request $request)
    {
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from duplicateAction.
     *
     * @param mixed $object
     *
     * @return Response|null
     */
    protected function preDuplicate($object)
    {
    }
}
