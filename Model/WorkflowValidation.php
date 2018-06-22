<?php
/**
 * @date        10/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Model;

use Pimcore\Model\AbstractModel;

/**
 * Class WorkflowValidation
 * @package Divante\WorkflowValidationBundle\Model
 */
class WorkflowValidation extends AbstractModel
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * @param int $id
     * @return WorkflowValidation|null
     */
    public static function getById(int $id)
    {
        $workflowValidation = new WorkflowValidation();

        try {
            $workflowValidation->getDao()->getById($id);
        } catch (\Exception $ex) {
            return null;
        }

        return $workflowValidation;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array $actions
     */
    public function setActions(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getActionConfig(string $name)
    {
        $config = null;

        foreach ($this->getActions() as $action) {
            if ($action['name'] === $name) {
                $config = $action;
                break;
            }
        }

        return $config;
    }

    /**
     * @param string $actionName
     * @param int $classId
     * @return array|null
     */
    public function getActionClassConfig(string $actionName, int $classId)
    {
        $config = null;

        $actionConfig = $this->getActionConfig($actionName);
        if (is_array($actionConfig)) {
            foreach ($actionConfig['classes'] as $class) {
                if ($class['id'] === $classId) {
                    $config = $class;
                    break;
                }
            }
        }

        return $config;
    }

    /**
     * @return WorkflowValidation\Dao
     */
    public function getDao()
    {
        return parent::getDao();
    }

    /**
     *
     */
    public function save()
    {
        $this->getDao()->save();
    }

    /**
     *
     */
    public function delete()
    {
        $this->getDao()->delete();
    }
}
