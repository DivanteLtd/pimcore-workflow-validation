<?php
/**
 * @date        16/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Validator;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;

/**
 * Class DefaultValidator
 * @package Divante\WorkflowValidationBundle\Validator
 */
class DefaultValidator implements ValidatorInterface
{
    /**
     * @var Concrete
     */
    protected $object;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return Concrete
     * @throws \UnexpectedValueException
     */
    public function getObject(): Concrete
    {
        if ($this->object === null) {
            $message = "Attribute named 'object' is empty";
            throw new \UnexpectedValueException($message);
        }

        return $this->object;
    }

    /**
     * @param Concrete $object
     */
    public function setObject(Concrete $object)
    {
        $this->object = $object;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function getName(): string
    {
        if ($this->name === null) {
            $message = "Attribute named 'name' is empty";
            throw new \UnexpectedValueException($message);
        }

        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     * @throws \UnexpectedValueException
     */
    public function getParams(): array
    {
        if ($this->params === null) {
            $message = "Attribute named 'params' is empty";
            throw new \UnexpectedValueException($message);
        }

        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $this->messages = [];

        $data  = $this->getData();
        $value = $this->getValue();
        try {
            $this->checkValidity($data, $value);
        } catch (ValidationException $ex) {
            if ($this->getObject()->getClass()->getAllowInherit()) {
                $getInheritedValues = $this->doGetInheritedValues();
                $value              = $this->getValue();
                try {
                    $this->checkValidity($data, $value);
                } catch (ValidationException $ex) {
                    $this->messages[] = $ex->getMessage();
                } finally {
                    $this->restoreGetInheritedValues($getInheritedValues);
                }
            } else {
                $this->messages[] = $ex->getMessage();
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
     * @return Data
     * @throws \UnexpectedValueException
     */
    protected function getData(): Data
    {
        $name = $this->getName();
        $data = $this->getObject()->getClass()->getFieldDefinition($name);

        if (!$data instanceof Data) {
            $message = sprintf("No data was found with key '%s'", $name);
            throw new \UnexpectedValueException($message);
        }

        return $data;
    }

    /**
     * @return bool
     */
    protected function doGetInheritedValues()
    {
        $getInheritedValues = AbstractObject::doGetInheritedValues();
        AbstractObject::setGetInheritedValues(true);

        return $getInheritedValues;
    }

    /**
     * @param bool $getInheritedValues
     */
    protected function restoreGetInheritedValues(bool $getInheritedValues)
    {
        AbstractObject::setGetInheritedValues($getInheritedValues);
    }

    /**
     * @return mixed
     */
    protected function getValue()
    {
        $name   = $this->getName();
        $getter = 'get' . ucfirst($name);

        return $this->getObject()->$getter();
    }

    /**
     * @param Data $data
     * @param mixed $value
     * @throws ValidationException
     */
    protected function checkValidity(Data $data, $value)
    {
        $exception = null;

        $mandatory = $data->getMandatory();
        $data->setMandatory(true);

        try {
            $data->checkValidity($value);
        } catch (ValidationException $ex) {
            $exception = $ex;
        } finally {
            $data->setMandatory($mandatory);
        }

        if ($exception instanceof ValidationException) {
            throw $ex;
        }
    }
}
