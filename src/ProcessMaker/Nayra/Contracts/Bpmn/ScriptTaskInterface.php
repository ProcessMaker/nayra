<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;


interface ScriptTaskInterface extends ActivityInterface
{
    const BPMN_PROPERTY_SCRIPT_FORMAT = 'scriptFormat';
    const BPMN_PROPERTY_SCRIPT = 'script';

    /**
     * Sets the script format of the script task
     *
     * @param $scriptFormat
     * @return mixed
     */
    public function setScriptFormat($scriptFormat);

    /**
     * Sets the script of the script task
     *
     * @param $script
     * @return mixed
     */
    public function setScript($script);

    /**
     * Returns the script format of the script task
     *
     * @param $stringFormat
     */
    public function getScriptFormat();

    /**
     * Returns de Script of the script task
     *
     * @param $script
     */
    public function getScript();
}