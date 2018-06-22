<?php
/**
 * @date        11/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Service;

use Divante\WorkflowValidationBundle\Model\WorkflowValidation;
use Pimcore\Model\Workflow;

/**
 * Class WorkflowValidationService
 * @package Divante\WorkflowValidationBundle\Service
 */
class WorkflowValidationService
{
    /**
     * @param int $id
     * @return Workflow
     * @throws \UnexpectedValueException
     */
    public function getWorkflow(int $id): Workflow
    {
        $workflow = Workflow::getById($id);
        if (!$workflow instanceof Workflow) {
            throw new \UnexpectedValueException();
        }

        $validation = WorkflowValidation::getById($id);
        if (!$validation instanceof WorkflowValidation) {
            $validation = $this->createWorkflowValidation($workflow);
        }

        $actions = $workflow->getActions();
        foreach ($actions as &$action) {
            $action['validation'] = ['classes' => []];
        }

        $workflowSubject = $workflow->getWorkflowSubject();
        if (in_array('object', $workflowSubject['types'])) {
            foreach ($actions as &$action) {
                foreach ($workflowSubject['classes'] as $classId) {
                    $config = $validation->getActionClassConfig($action['name'], $classId);
                    if (is_array($config)) {
                        $action['validation']['classes'][] = $config;
                    } else {
                        $action['validation']['classes'][] = ['id' => $classId, 'rules' => []];
                    }
                }
            }
        }

        $workflow->setActions($actions);

        return $workflow;
    }

    /**
     * @param int $id
     * @param array $data
     * @throws \UnexpectedValueException
     */
    public function updateWorkflow(int $id, array $data)
    {
        $actions = [];
        foreach ($data['actions'] as &$action) {
            $actions[] = [
                'name'    => $action['name'],
                'classes' => $action['validation']['classes'],
            ];
            unset($action['validation']);
        }

        $workflow = Workflow::getById($id);
        if (!$workflow instanceof Workflow) {
            throw new \UnexpectedValueException();
        }

        $classes       = $data['settings']['classes'];
        $types         = $data['settings']['types'];
        $assetTypes    = $data['settings']['assetTypes'];
        $documentTypes = $data['settings']['documentTypes'];

        $workflowSubject = [
            'types'         => $types,
            'classes'       => $classes,
            'assetTypes'    => $assetTypes,
            'documentTypes' => $documentTypes
        ];

        $workflow->setValues($data['settings']);
        $workflow->setWorkflowSubject($workflowSubject);
        $workflow->setStates($data['states']);
        $workflow->setStatuses($data['statuses']);
        $workflow->setActions($data['actions']);
        $workflow->setTransitionDefinitions($data['transitionDefinitions']);
        $workflow->save();

        $validation = WorkflowValidation::getById($id);
        if (!$validation instanceof WorkflowValidation) {
            $validation = new WorkflowValidation();
            $validation->setId($id);
        }

        $validation->setActions($actions);
        $validation->save();
    }

    /**
     * @param int $id
     */
    public function deleteWorkflow(int $id)
    {
        $validation = WorkflowValidation::getById($id);
        if ($validation instanceof WorkflowValidation) {
            $validation->delete();
        }

        $workflow = Workflow::getById($id);
        if ($workflow instanceof Workflow) {
            $workflow->delete();
        }
    }

    /**
     * @param Workflow $workflow
     * @return WorkflowValidation
     */
    public function createWorkflowValidation(Workflow $workflow): WorkflowValidation
    {
        $actions = [];

        foreach ($workflow->getActions() as $action) {
            $actions[] = [
                'name'    => $action['name'],
                'classes' => [],
            ];
        }

        $workflowSubject = $workflow->getWorkflowSubject();
        if (in_array('object', $workflowSubject['types'])) {
            foreach ($actions as &$action) {
                foreach ($workflowSubject['classes'] as $classId) {
                    $action['classes'][] = [
                        'id'    => $classId,
                        'rules' => [],
                    ];
                }
            }
        }

        $validation = new WorkflowValidation();
        $validation->setId($workflow->getId());
        $validation->setActions($actions);

        return $validation;
    }
}
