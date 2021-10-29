<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Base implementation for a exclusive gateway.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EventDefinitionTrait
{
    use BaseTrait;

    /**
     * Register event with a catch event
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     */
    public function registerWithCatchEvent(EngineInterface $engine, CatchEventInterface $element)
    {
        $engine->getEventDefinitionBus()->registerCatchEvent($element, $this, function (EventDefinitionInterface $eventDefinition, ExecutionInstanceInterface $instance = null, TokenInterface $token = null) use ($element) {
            $element->execute($eventDefinition, $instance, $token);
        });
    }

    /**
     * Occures when the catch event was activated
     *
     * @param EngineInterface $engine
     * @param CatchEventInterface $element
     * @param TokenInterface|null $token
     *
     * @return void
     */
    public function catchEventActivated(EngineInterface $engine, CatchEventInterface $element, TokenInterface $token = null)
    {
    }

    /**
     * Check if the event definition should be catch
     *
     * @param EventDefinitionInterface $sourceEvent
     *
     * @return bool
     */
    public function shouldCatchEventDefinition(EventDefinitionInterface $sourceEvent)
    {
        return true;
    }

    /**
     * Get data contained in the event payload
     *
     * @param TokenInterface|null $token
     *
     * @return mixed
     */
    public function getPayloadData(TokenInterface $token = null)
    {
        return $token ? $token->getInstance()->getDataStore()->getData() : [];
    }
}
