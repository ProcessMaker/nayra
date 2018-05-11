<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Behavior that must implement all event messages
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface SignalInterface
{
    /**
     * Returns the id of the message
     *
     * @return string
     */
    public function getId();

    /**
     * Sets the id of the message
     * @param string $value
     */
    public function setId($value);

    /**
     * Returns the name of the message
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name of the signal
     * @param string $value
     */
    public function setName($value);


        /**
     * Sets the message flow to which this message pertains
     *
     * @param $messageFlow
     *
     * @return mixed
     */
    public function setMessageFlow($messageFlow);


    /**
     * Returns the message flow to which this message pertains
     *
     * @return mixed
     */
    public function getMessageFlow();

}
