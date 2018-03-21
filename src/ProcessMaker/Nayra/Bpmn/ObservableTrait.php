<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;

/**
 * Observable behavior.
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait ObservableTrait
{
    private $observers = [];

    /**
     * Attach a callback to an event.
     *
     * @param $event
     * @param $callback
     */
    public function attachEvent($event, $callback)
    {
        $this->observers[$event][] = $callback;
    }

    /**
     * Detach a callback from an event.
     *
     * @param $event
     * @param $callback
     */
    public function detachEvent($event, $callback)
    {
        $index = array_search($callback, $this->observers[$event], true);
        if ($index !== false) {
            unset($this->observers[$event][$index]);
        }
    }

    /**
     * Notify a event to the observers.
     *
     * @param $event
     * @param array ...$arguments
     */
    protected function notifyEvent($event, ...$arguments)
    {
        if (empty($this->observers[$event])) {
            return;
        }
        foreach ($this->observers[$event] as $callback) {
            call_user_func_array($callback, $arguments);
        }
    }
}
