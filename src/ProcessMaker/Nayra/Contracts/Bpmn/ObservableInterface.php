<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Observable interface.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface ObservableInterface
{

    /**
     * Attach a callback to an event.
     *
     * @param $event
     * @param $callback
     */
    public function attachEvent($event, $callback);

    /**
     * Detach a callback from an event.
     *
     * @param $event
     * @param $callback
     */
    public function detachEvent($event, $callback);
}
