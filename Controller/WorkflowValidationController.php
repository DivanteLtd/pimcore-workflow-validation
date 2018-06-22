<?php
/**
 * @date        11/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Controller;

use Divante\WorkflowValidationBundle\Model\WorkflowValidation;
use Divante\WorkflowValidationBundle\Service\WorkflowValidationService;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model\Workflow;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class WorkflowValidationController
 * @package Divante\WorkflowValidationBundle\Controller
 * @Route("/workflow-validation")
 */
class WorkflowValidationController extends AdminController
{
    /**
     * @param Request $request
     * @param WorkflowValidationService $service
     * @return JsonResponse
     * @Route("/get")
     */
    public function getAction(Request $request, WorkflowValidationService $service): JsonResponse
    {
        $id = (int) $request->get('id', 0);

        try {
            $workflow = $service->getWorkflow($id);
        } catch (\Exception $ex) {
            return $this->adminJson(['success' => false]);
        }

        return $this->adminJson([
            'success'  => true,
            'workflow' => get_object_vars($workflow),
        ]);
    }

    /**
     * @param Request $request
     * @param WorkflowValidationService $service
     * @return JsonResponse
     * @Route("/update")
     */
    public function updateAction(Request $request, WorkflowValidationService $service): JsonResponse
    {
        $id   = (int) $request->get('id', 0);
        $data = $this->decodeJson($request->get('data', '[]'));

        try {
            $service->updateWorkflow($id, $data);
            $workflow = $service->getWorkflow($id);
        } catch (\Exception $ex) {
            return $this->adminJson(['success' => false]);
        }

        return $this->json([
            'success'  => true,
            'workflow' => get_object_vars($workflow)
        ]);
    }

    /**
     * @param Request $request
     * @param WorkflowValidationService $service
     * @return JsonResponse
     * @Route("/delete")
     */
    public function deleteAction(Request $request, WorkflowValidationService $service): JsonResponse
    {
        $id = (int) $request->get('id', 0);
        $service->deleteWorkflow($id);

        return $this->adminJson(['success' => true]);
    }
}
