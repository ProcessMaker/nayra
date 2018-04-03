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
}
