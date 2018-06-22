<?php
/**
 * @date        16/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Validator;

use Pimcore\Model\DataObject\Concrete;

/**
 * Interface ValidatorInterface
 * @package Divante\WorkflowValidationBundle\Validator
 */
interface ValidatorInterface
{
    /**
     * @return Concrete
     */
    public function getObject(): Concrete;

    /**
     * @param Concrete $object
     */
    public function setObject(Concrete $object);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * @return bool
     */
    public function isValid(): bool;

    /**
     * @return array
     */
    public function getMessages(): array;
}
