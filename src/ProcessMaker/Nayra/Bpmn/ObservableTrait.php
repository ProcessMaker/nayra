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
     *  Returns the list of observers of the object
     *
     * @return array
     */
    public function getObservers()
    {
        return $this->observers;
    }

    /**
     * Attach a callback to an event.
     *
     * @param string $event
     * @param callable $callback
     */
    public function attachEvent($event, callable $callback)
    {
        $this->observers[$event][] = $callback;
    }

    /**
     * Detach a callback from an event.
     *
     * @param string $event
     * @param callable $callback
     */
    public function detachEvent($event, callable $callback)
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

    /**
     * Notify an external event to the observers.
     *
     * @param $event
     * @param array ...$arguments
     */
    public function notifyExternalEvent($event, ...$arguments)
    {
        $this->notifyEvent($event, ...$arguments);
    }
}
