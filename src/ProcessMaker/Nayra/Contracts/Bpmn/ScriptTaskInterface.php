<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Defines the interface to be used by the ScriptTasks
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ScriptTaskInterface extends ActivityInterface
{
    const BPMN_PROPERTY_SCRIPT_FORMAT = 'scriptFormat';
    const BPMN_PROPERTY_SCRIPT = 'script';

    /**
     * Sets the script format of the script task
     *
     * @param string $scriptFormat
     */
    public function setScriptFormat($scriptFormat);

    /**
     * Sets the script of the script task
     *
     * @param string $script
     */
    public function setScript($script);

    /**
     * Returns the script format of the script task
     *
     * @return string
     */
    public function getScriptFormat();

    /**
     * Returns de Script of the script task
     *
     * @return $string
     */
    public function getScript();
}
