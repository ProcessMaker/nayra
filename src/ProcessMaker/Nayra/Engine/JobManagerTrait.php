<?php

namespace ProcessMaker\Nayra\Engine;

use DateTimeInterface;
use ProcessMaker\Nayra\Bpmn\Models\DatePeriod;

/**
 * JobManagerTrait
 */
trait JobManagerTrait
{
    /**
     * Get the next datetime event of a cycle.
     *
     * @param DatePeriod $cycle
     * @param DateTimeInterface $last
     *
     * @return DateTimeInterface
     */
    protected function getNextDateTimeCycle(DatePeriod $cycle, DateTimeInterface $last)
    {
        $nextDateTime = null;
        foreach ($cycle as $i => $dateTime) {
            if ($last < $dateTime) {
                $nextDateTime = $dateTime;
                break;
            }
        }

        return $nextDateTime;
    }
}
