<?php

namespace ProcessMaker\Nayra\Bpmn\Model;

use Exception;
use ProcessMaker\Nayra\Bpmn\ActivityTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * This activity will raise an exception when executed.
 *
 */
class ScriptTask implements ScriptTaskInterface
{

    use ActivityTrait;

    /**
     * Configure the activity to evaluate script tasks
     *
     */
    protected function initActivity()
    {
        $this->attachEvent(
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            function ($self, TokenInterface $token) {
                $this->notifyEvent(ScriptTaskInterface::EVENT_SCRIPT_TASK_ACTIVATED, $this, $token);
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
        ];
    }

    /**
     * Get called element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface
     */
    public function getCalledElement()
    {
        return $this->getProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT);
    }

    /**
     * Set called element.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\CallableElementInterface $callableElement
     *
     * @return $this
     */
    public function setCalledElement(CallableElementInterface $callableElement)
    {
        $this->setProperty(CallActivityInterface::BPMN_PROPERTY_CALLED_ELEMENT, $callableElement);
        return $this;
    }

    /**
     * Sets the script format of the script task
     *
     * @param string $scriptFormat
     */
    public function setScriptFormat($scriptFormat)
    {
        $this->setProperty(ScriptTaskInterface::BPMN_PROPERTY_SCRIPT_FORMAT, $scriptFormat);
    }

    /**
     * Sets the script of the script task
     *
     * @param string $script
     */
    public function setScript($script)
    {
        $this->setProperty(ScriptTaskInterface::BPMN_PROPERTY_SCRIPT, $script);
    }

    /**
     * Returns the script format of the script task
     *
     * @return string
     */
    public function getScriptFormat()
    {
        return $this->getProperty(ScriptTaskInterface::BPMN_PROPERTY_SCRIPT_FORMAT);
    }

    /**
     * Returns de Script of the script task
     *
     * @return $string
     */
    public function getScript()
    {
        return $this->getProperty(ScriptTaskInterface::BPMN_PROPERTY_SCRIPT);
    }

    /**
     * Runs the ScriptTask
     * @param TokenInterface $token
     */
    public function runScript(TokenInterface $token)
    {
        //if the script runs correctly complete te activity, otherwise set the token to failed state
        if ($this->executeScript($token, $this->getScript())) {
            $this->complete($token);
        }
        else {
            $token->setStatus(ActivityInterface::TOKEN_STATE_FAILING);
        }
    }

    /**
     * Script runner fot testing purposes that just evaluates the sent php code
     *
     * @param TokenInterface $token
     * @param string $script
     * @return bool
     */
    private function executeScript(TokenInterface $token, $script)
    {
        $result = true;
        try {
            eval ($script);
        }
        catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

}
