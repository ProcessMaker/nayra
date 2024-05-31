<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Sub-Process/Call Activity base implementation.
 */
trait ActivitySubProcessTrait
{
    use ActivityTrait;

    /**
     * Configure the activity to go to a FAILING status when activated.
     */
    protected function initActivity()
    {
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function ($self, TokenInterface $token) {
                $instance = $this->callSubprocess($token);
                $this->linkProcesses($token, $instance);
            }
        );
    }

    /**
     * Call the subprocess
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     *
     * @return ExecutionInstanceInterface
     */
    protected function callSubprocess(TokenInterface $token)
    {
        $dataStore = $this->getRepository()->createDataStore();
        $dataStore->setData($token->getInstance()->getDataStore()->getData());

        return $this->getCalledElement()->call($dataStore);
    }

    /**
     * Links parent and sub process in a CallActivity
     *
     * @param TokenInterface $token
     * @param ExecutionInstanceInterface $instance
     *
     * @return void
     */
    private function linkProcesses(TokenInterface $token, ExecutionInstanceInterface $instance)
    {
        $this->getCalledElement()->attachEvent(
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
            function ($self, ExecutionInstanceInterface $closedInstance) use ($token, $instance) {
                $skipStates = [
                    ActivityInterface::TOKEN_STATE_FAILING,
                    ActivityInterface::TOKEN_STATE_INTERRUPTED,
                ];
                if ($closedInstance->getId() === $instance->getId()
                && !in_array($token->getStatus(), $skipStates)) {
                    $this->completeSubprocess($token, $closedInstance, $instance);
                }
            }
        );
        $this->getCalledElement()->attachEvent(
            ErrorEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,
            function ($element, $innerToken, $error) use ($token, $instance) {
                if ($innerToken->getInstance() === $instance) {
                    $this->catchSubprocessError($token, $error, $instance);
                }
            }
        );
        $this->getCalledElement()->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_EXCEPTION,
            function ($element, $innerToken, $error) use ($token, $instance) {
                $elementHasErrorBoundary = false;
                foreach($element->getBoundaryEvents() as $boundary) {
                    if ($boundary->getEventDefinitions()->item(0) instanceof ErrorEventDefinitionInterface) {
                        $elementHasErrorBoundary = true;
                    }
                }
                if (!$elementHasErrorBoundary && $innerToken->getInstance() === $instance) {
                    $this->catchSubprocessError($token, $error, $instance);
                }
            }
        );
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_CANCELLED,
            function ($activity, $transition, $tokens) use ($token, $instance) {
                $belongsTo = false;
                foreach ($tokens as $cancelled) {
                    $belongsTo |= $cancelled->getInstance()->getId() === $token->getInstance()->getId();
                }
                $belongsTo ? $this->cancelSubprocess($instance) : null;
            }
        );
    }

    /**
     * Complete the subprocess
     *
     * @param TokenInterface $token
     * @return void
     */
    protected function completeSubprocess(TokenInterface $token)
    {
        $token->setStatus(ActivityInterface::TOKEN_STATE_COMPLETED);
    }

    /**
     * Catch a subprocess error
     *
     * @param TokenInterface $token
     * @param ErrorInterface|null $error
     *
     * @return ActivityInterface
     */
    protected function catchSubprocessError(TokenInterface $token, ErrorInterface $error = null)
    {
        $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        $token->setProperty(ErrorEventDefinitionInterface::BPMN_PROPERTY_ERROR, $error);

        return $this;
    }

    /**
     * Cancel a subprocess
     *
     * @param ExecutionInstanceInterface|null $instance
     *
     * @return ActivityInterface
     */
    protected function cancelSubprocess(ExecutionInstanceInterface $instance = null)
    {
        $instance ? $instance->close() : null;

        return $this;
    }
}
