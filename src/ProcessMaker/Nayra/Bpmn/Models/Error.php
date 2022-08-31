<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\BaseTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;

class Error implements ErrorInterface
{
    use BaseTrait;

    /**
     * @var MessageFlowInterface
     */
    private $messageFlow;

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName()
    {
        return $this->getProperty(ErrorInterface::BPMN_PROPERTY_NAME);
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->getProperty(ErrorInterface::BPMN_PROPERTY_ERROR_CODE);
    }
}
