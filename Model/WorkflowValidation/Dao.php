<?php
/**
 * @date        10/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Model\WorkflowValidation;

use Divante\WorkflowValidationBundle\Model\WorkflowValidation;
use Pimcore\Model\Dao\PhpArrayTable;

/**
 * Class Dao
 * @package Divante\WorkflowValidationBundle\Model\WorkflowValidation
 */
class Dao extends PhpArrayTable
{
    /**
     *
     */
    public function configure()
    {
        parent::configure();
        $this->setFile('workflowvalidation');
    }

    /**
     * @return WorkflowValidation
     */
    public function getModel(): WorkflowValidation
    {
        return $this->model;
    }

    /**
     * @param int $id
     * @throws \Exception
     */
    public function getById(int $id)
    {
        $data = $this->db->getById($id);

        if (!is_array($data)) {
            $message = "Workflow validation with ID '" . $id . "' doesn't exist";
            throw new \Exception($message);
        }

        $this->getModel()->setValues($data);
    }

    /**
     *
     */
    public function save()
    {
        $model = $this->getModel();

        $data = [];
        foreach ($model->getObjectVars() as $name => $value) {
            $getter = 'get' . ucfirst($name);
            $data[$name] = $model->$getter();
        }

        $id = $model->getId();
        $this->db->insertOrUpdate($data, $id);

        if (!$id) {
            $model->setId($this->db->getLastInsertId());
        }
    }

    /**
     *
     */
    public function delete()
    {
        $id = $this->getModel()->getId();
        if ($id) {
            $this->db->delete($id);
        }
    }
}
