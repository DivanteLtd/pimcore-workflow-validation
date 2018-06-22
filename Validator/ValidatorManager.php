<?php
/**
 * @date        16/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Validator;

use Divante\WorkflowValidationBundle\Model\WorkflowValidation;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\WorkflowManagement\Workflow\Manager as WorkflowManager;
use Psr\Container\ContainerInterface;

/**
 * Class ValidatorManager
 * @package Divante\WorkflowValidationBundle\Validator
 */
class ValidatorManager
{
    /**
     * @var ContainerInterface
     */
    protected $handlerLocator;

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * ValidatorManager constructor.
     * @param ContainerInterface $handlerLocator
     */
    public function __construct(ContainerInterface $handlerLocator)
    {
        $this->setHandlerLocator($handlerLocator);
    }

    /**
     * @return ContainerInterface
     */
    public function getHandlerLocator(): ContainerInterface
    {
        return $this->handlerLocator;
    }

    /**
     * @param ContainerInterface $handlerLocator
     */
    public function setHandlerLocator(ContainerInterface $handlerLocator)
    {
        $this->handlerLocator = $handlerLocator;
    }

    /**
     * @return WorkflowManager
     * @throws \UnexpectedValueException
     */
    public function getWorkflowManager(): WorkflowManager
    {
        if ($this->workflowManager === null) {
            $message = "Attribute named 'workflowManager' is empty";
            throw new \UnexpectedValueException($message);
        }

        return $this->workflowManager;
    }

    /**
     * @param WorkflowManager $workflowManager
     */
    public function setWorkflowManager(WorkflowManager $workflowManager)
    {
        $this->workflowManager = $workflowManager;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $this->messages = [];

        if (!$this->isDefaultAction()) {
            foreach ($this->getValidationRules() as $fieldName => $params) {
                $id = $this->getHandlerId($fieldName);

                if ($this->getHandlerLocator()->has($id)) {
                    /** @var ValidatorInterface $validator */
                    $validator = $this->getHandlerLocator()->get($id);

                    $validator->setObject($this->getObject());
                    $validator->setName($fieldName);
                    $validator->setParams($params);

                    if (!$validator->isValid()) {
                        $this->messages = array_merge($this->messages, $validator->getMessages());
                    }
                }
            }
        }

        return empty($this->messages);
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    protected function isDefaultAction(): bool
    {
        $workflow   = $this->getWorkflowManager()->getWorkflow();
        $actionData = $this->getWorkflowManager()->getActionData();

        $defaultState  = $workflow->getDefaultState();
        $defaultStatus = $workflow->getDefaultStatus();

        return $defaultState === $actionData['newState'] && $defaultStatus === $actionData['newStatus'];
    }

    /**
     * @return array
     */
    protected function getValidationRules(): array
    {
        $workflowId = $this->getWorkflowManager()->getWorkflow()->getId();
        $validation = WorkflowValidation::getById($workflowId);

        if (!$validation instanceof WorkflowValidation) {
            return [];
        }

        $actionName = $this->getWorkflowManager()->getActionData()['action'];
        $classId    = $this->getObject()->getClassId();
        $config     = $validation->getActionClassConfig($actionName, $classId);

        if (!is_array($config)) {
            return [];
        }

        $rules = [];

        foreach ($config['rules'] as $rule) {
            $field = explode('.', $rule);
            $name  = $field[0];

            if (!isset($rules[$name])) {
                $rules[$name] = [];
            }

            if (count($field) > 1) {
                $rules[$name][] = $field[1];
            }
        }

        return $rules;
    }

    /**
     * @return Concrete
     */
    protected function getObject(): Concrete
    {
        $element = $this->getWorkflowManager()->getElement();

        if (!$element instanceof Concrete) {
            $message = "Instance of '%s' is expected, but '%s' is given";
            throw new \UnexpectedValueException(sprintf($message, Concrete::class, get_class($element)));
        }

        return $element;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getHandlerId(string $name): string
    {
        $data = $this->getObject()->getClass()->getFieldDefinition($name, ['suppressEnrichment' => true]);

        if (!$data instanceof Data) {
            $message = sprintf("No data was found with key '%s'", $name);
            throw new \UnexpectedValueException($message);
        }

        if ($data instanceof Data\Localizedfields) {
            return 'localizedfields';
        }

        return 'default';
    }
}
