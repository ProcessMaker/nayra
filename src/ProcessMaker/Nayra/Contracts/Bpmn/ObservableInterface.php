<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Observable interface.
 */
interface ObservableInterface
{
    /**
     *  Returns the list of observers of the object
     *
     * @return array
     */
    public function getObservers();

    /**
     *  Returns the list of observers of the object
     *
     * @return array
     */
    /**
     * Attach a callback to an event.
     *
     * @param string $event
     * @param callable $callback
     */
    public function attachEvent($event, callable $callback);

    /**
     * Detach a callback from an event.
     *
     * @param string $event
     * @param callable $callback
     */
    public function detachEvent($event, callable $callback);

    /**
     * Notify an external event to the observers.
     *
     * @param $event
     * @param array ...$arguments
     */
    public function notifyExternalEvent($event, ...$arguments);
}
