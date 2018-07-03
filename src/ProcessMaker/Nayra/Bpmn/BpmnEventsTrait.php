<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface;
use ReflectionClass;

/**
 * Trait to implements bpmn events handling.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait BpmnEventsTrait
{
    use ObservableTrait {
        notifyEvent as private internalNotifyEvent;
    }

    /**
     * Array map of custom event classes for the bpmn element.
     *
     * @return array
     */
    abstract protected function getBpmnEventClasses();

    /**
     * Fire a event for the bpmn element.
     *
     * @param string $event
     * @param array ...$arguments
     */
    protected function notifyEvent($event, ...$arguments)
    {
        switch ($event) {
            case ThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES:
                if ($arguments[0] instanceof EndEventInterface) {
                    foreach ($this->getOwnerProcess()->getInstances()->toArray() as $instance) {
                        $this->getRepository()
                            ->getTokenRepository()
                            ->persistThrowEventTokenArrives($arguments[0], $arguments[1]);
                    }
                }

                if ($arguments[0] instanceof ThrowEventInterface) {
                    foreach ($this->getOwnerProcess()->getInstances()->toArray() as $instance) {
                        $this->getRepository()
                            ->getTokenRepository()
                            ->persistThrowEventTokenArrives($arguments[0], $arguments[1]);
                    }
                }
            break;
        }


        $bpmnEvents = $this->getBpmnEventClasses();
        if (isset($bpmnEvents[$event])) {
            $reflector = new ReflectionClass($bpmnEvents[$event]);
            $payload = $reflector->newInstanceArgs($arguments);
        } else {
            $payload = $arguments;
        }
        $this->getOwnerProcess()->getDispatcher()->dispatch($event, $payload);
        array_unshift($arguments, $event);
        call_user_func_array([$this, 'internalNotifyEvent'], $arguments);
        call_user_func_array([$this->getOwnerProcess(), 'notifyEvent'], $arguments);
    }
}
