<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;


interface IntermediateTimerEventInterface extends EventInterface
{
    const EVENT_TIMER_TOKEN_ARRIVES = 'TimerEventTokenArrives';
    const EVENT_TIMER_EXCEPTION = 'TimerEventException';
    const EVENT_TIMER_TOKEN_PASSED = 'TimerEventTokenPassed';
    const EVENT_TIMER_TOKEN_CONSUMED = 'TimerEventTokenConsumed';
    const EVENT_TIMER_TOKEN_TIMER = 'TimerEventTokenTimer';
}

