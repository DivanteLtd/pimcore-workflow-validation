<?php
/**
= * @date        16/11/2017
 * @author      Korneliusz Kirsz <kkirsz@divante.pl>
 * @copyright   Copyright (c) 2017 DIVANTE (http://divante.pl)
 */

declare(strict_types=1);

namespace Divante\WorkflowValidationBundle\EventListener;

use Divante\WorkflowValidationBundle\Validator\ValidatorManager;
use Pimcore\Event\Model\WorkflowEvent;
use Pimcore\Event\WorkflowEvents;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WorkflowListener
 * @package Divante\WorkflowValidationBundle\EventListener
 */
class WorkflowListener implements EventSubscriberInterface
{
    /**
     * @var ValidatorManager
     */
    protected $validationManager;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            WorkflowEvents::PRE_ACTION => 'onPreAction',
        ];
    }

    /**
     * WorkflowListener constructor.
     * @param ValidatorManager $validatorManager
     */
    public function __construct(ValidatorManager $validatorManager)
    {
        $this->validationManager = $validatorManager;
    }

    /**
     * @param WorkflowEvent $event
     * @throws \Exception
     */
    public function onPreAction(WorkflowEvent $event)
    {
        $manager = $event->getWorkflowManager();
        $element = $manager->getElement();

        if ($element instanceof Concrete) {
            $this->validationManager->setWorkflowManager($manager);
            if (!$this->validationManager->isValid()) {
                throw new \Exception(implode('<br>', $this->validationManager->getMessages()));
            }
        }
    }
}
