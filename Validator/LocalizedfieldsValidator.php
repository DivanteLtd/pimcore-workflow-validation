<?php
/**
 * @date        17/04/2018
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2018 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\Validator;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Localizedfield;
use Pimcore\Model\Element\ValidationException;

/**
 * Class LocalizedfieldsValidator
 * @package Divante\WorkflowValidationBundle\Validator
 */
class LocalizedfieldsValidator extends DefaultValidator
{
    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $this->messages = [];

        $value          = $this->getValue();
        $this->messages = $this->validate($value);

        if (empty($this->messages)) {
            return true;
        }

        if ($this->getObject()->getClass()->getAllowInherit()) {
            $getInheritedValues = $this->doGetInheritedValues();
            $value = $this->getValue();
            $this->messages = $this->validate($value);
            $this->restoreGetInheritedValues($getInheritedValues);
        }

        return empty($this->messages);
    }

    /**
     * @param Localizedfield $value
     * @return array
     * @throws \UnexpectedValueException
     */
    protected function validate(Localizedfield $value): array
    {
        $messages = [];

        $data      = $this->getData();
        $languages = $this->getLanguages();

        foreach ($this->getParams() as $subname) {
            $subdata = $data->getFielddefinition($subname);

            if (!$subdata instanceof Data) {
                $message = sprintf("No data was found with key '%s'", $subname);
                throw new \UnexpectedValueException($message);
            }

            foreach ($languages as $language) {
                $subvalue = $value->getLocalizedValue($subname, $language);
                try {
                    $this->checkValidity($subdata, $subvalue);
                } catch (ValidationException $ex) {
                    $messages[] = $ex->getMessage() . ' (' . $language . ')';
                }
            }
        }

        return $messages;
    }

    /**
     * @return Data\Localizedfields
     */
    protected function getData(): Data
    {
        return parent::getData();
    }

    /**
     * @return Localizedfield
     */
    protected function getValue()
    {
        return parent::getValue();
    }

    /**
     * @return array
     */
    protected function getLanguages(): array
    {
        $languages = [];

        $conf = \Pimcore\Config::getSystemConfig();
        if ($conf->general->validLanguages) {
            $languages = explode(',', $conf->general->validLanguages);
        }

        return $languages;
    }
}
