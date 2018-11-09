<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use Exception;
use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Bpmn\Events\ActivityActivatedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityClosedEvent;
use ProcessMaker\Nayra\Bpmn\Events\ActivityCompletedEvent;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ServiceTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * This activity will raise an exception when executed.
 *
 */
class ServiceTask implements ServiceTaskInterface
{

    use ActivityTrait;

    /**
     * Initialize the service task
     *
     */
    protected function initServiceTask()
    {
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function ($self, TokenInterface $token) {
                $this->notifyEvent(ServiceTaskInterface::EVENT_SERVICE_TASK_ACTIVATED, $this, $token);
            }
        );
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    protected function getBpmnEventClasses()
    {
        return [
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED => ActivityActivatedEvent::class,
            ActivityInterface::EVENT_ACTIVITY_COMPLETED => ActivityCompletedEvent::class,
            ActivityInterface::EVENT_ACTIVITY_CLOSED => ActivityClosedEvent::class,
        ];
    }

    /**
     * Sets the service task implementation
     *
     * @param mixed $implementation
     *
     * @return $this
     */
    public function setImplementation($implementation)
    {
        $this->setProperty(ServiceTaskInterface::BPMN_PROPERTY_IMPLEMENTATION, $implementation);
        return $this;
    }

    /**
     * Returns the service task implementation
     *
     * @return mixed
     */
    public function getImplementation()
    {
        return $this->getProperty(ServiceTaskInterface::BPMN_PROPERTY_IMPLEMENTATION);
    }

    /**
     * Runs the Service Task
     *
     * @param TokenInterface $token
     *
     * @return $this
     */
    public function run(TokenInterface $token)
    {
        //if the script runs correctly complete te activity, otherwise set the token to failed state
        if ($this->executeService($token, $this->getImplementation())) {
            $this->complete($token);
        }
        else {
            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        }
        return $this;
    }

    /**
     * Service Task runner for testing purposes
     *
     * @param TokenInterface $token
     * @param mixed $implementation
     *
     * @return bool
     */
    private function executeService(TokenInterface $token, $implementation)
    {
        $result = true;
        try {
            $callable = is_string($implementation) && strpos($implementation, '@')
                ? explode('@', $implementation) : $implementation;
            call_user_func($callable);
        }
        catch (Exception $exception) {
            $result = false;
        }
        return $result;
    }

}
