<?php

namespace ProcessMaker\Nayra\Bpmn;

use DateInterval;
use DatePeriod as GlobalDatePeriod;
use DateTime;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\DatePeriod;

/**
 * Tests for the DatePeriod class
 *
 * @package ProcessMaker\Nayra\Bpmn\Models
 */
class DatePeriodTest extends TestCase
{
    /**
     * Tests DatePeriod with different time zones
     */
    public function testPeriodsWithDifferentTimeZones()
    {
        $cycle = new DatePeriod('R/2018-10-02T08:00:00Z/P1D/2018-10-07T08:00:00-04:00');
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-07 12:00:00', $cycle->end->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->end);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-07 12:00:00', $cycle->end->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod of type R[n]/start/interval/end
     */
    public function testPeriodsCompleteIso8601String()
    {
        $cycle = new DatePeriod('R3/2018-10-02T08:00:00Z/P1D/2018-10-07T08:00:00-04:00');
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-07 12:00:00', $cycle->end->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, [$cycle->end, $cycle->recurrences - 1]);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-07 12:00:00', $cycle->end->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without end
     */
    public function testPeriodsWithoutEnd()
    {
        $cycle = new DatePeriod('R/2018-10-02T08:00:00Z/P1D');
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without start
     */
    public function testPeriodsWithoutStart()
    {
        $cycle = new DatePeriod('R/P1D/2018-10-02T08:00:00Z');
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->end->format('Y-m-d H:i:s'));
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->end);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->end->format('Y-m-d H:i:s'));
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without start nor end
     */
    public function testPeriodsWithoutStartNorEnd()
    {
        $cycle = new DatePeriod('R/P1D');
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->end);
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(DatePeriod::INF_RECURRENCES, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod with different time zones, and with repeating number
     */
    public function testPeriodsWithRepeatingNumber()
    {
        $cycle = new DatePeriod('R3/2018-10-02T08:00:00Z/P1D');
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        // Assertion: Recurrences = Repetitions -1
        $this->assertEquals(4, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->recurrences - 1);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(4, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without end, but with repeating number
     */
    public function testPeriodsWithoutEndWithRepeatingNumber()
    {
        $cycle = new DatePeriod('R3/2018-10-02T08:00:00Z/P1D');
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(4, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->recurrences - 1);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->start->format('Y-m-d H:i:s'));
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(4, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without start, but with repeating number
     */
    public function testPeriodsWithoutStartWithRepeatingNumber()
    {
        $cycle = new DatePeriod('R3/P1D/2018-10-02T08:00:00Z');
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->end->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, [$cycle->end, $cycle->recurrences - 1]);
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals('2018-10-02 08:00:00', $cycle->end->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $cycle->recurrences);
    }

    /**
     * Tests DatePeriod without start nor end, but with repeating number
     */
    public function testPeriodsWithoutStartNorEndWithRepeatingNumber()
    {
        $cycle = new DatePeriod('R3/P1D');
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(4, $cycle->recurrences);

        $cycle = new DatePeriod($cycle->start, $cycle->interval, $cycle->recurrences - 1);
        $this->assertEquals(null, $cycle->start);
        $this->assertArraySubset(['y' => 0, 'm' => 0, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0], (array)$cycle->interval);
        $this->assertEquals(null, $cycle->end);
        $this->assertEquals(4, $cycle->recurrences);
    }

    /**
     * Compare DatePeriod with GlobalDatePeriod
     *
     */
    public function testStandardDatePeriod()
    {
        // Compare standar initialization
        $cycle0 = new GlobalDatePeriod('R3/2018-10-02T08:00:00Z/P1D');
        $cycle = new DatePeriod('R3/2018-10-02T08:00:00Z/P1D');//new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), 2);
        $this->assertEquals($cycle0->start, $cycle->start);
        $this->assertEquals($cycle0->interval, $cycle->interval);
        $this->assertEquals($cycle0->end, $cycle->end);
        $this->assertEquals($cycle0->recurrences, $cycle->recurrences);

        // Compare parameter initialization
        $cycle0 = new GlobalDatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), 2);
        $cycle = new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), 2);
        $this->assertEquals($cycle0->start, $cycle->start);
        $this->assertEquals($cycle0->interval, $cycle->interval);
        $this->assertEquals($cycle0->end, $cycle->end);
        $this->assertEquals($cycle0->recurrences, $cycle->recurrences);

        // Compare json_encode
        $this->assertEquals(json_encode($cycle), json_encode($cycle0));

        // Compare parameter initialization
        $cycle0 = new GlobalDatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), 0);
        $cycle = new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), 0);
        $this->assertEquals($cycle0->start, $cycle->start);
        $this->assertEquals($cycle0->interval, $cycle->interval);
        $this->assertEquals($cycle0->end, $cycle->end);
        $this->assertEquals($cycle0->recurrences, $cycle->recurrences);

        // Compare parameter initialization
        $cycle0 = new GlobalDatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), -1);
        $cycle = new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), -1);
        $this->assertEquals($cycle0->start, $cycle->start);
        $this->assertEquals($cycle0->interval, $cycle->interval);
        $this->assertEquals($cycle0->end, $cycle->end);
        $this->assertEquals($cycle0->recurrences, $cycle->recurrences);

        // Compare parameter initialization without end.
        // Recurrences are not the same, because DatePeriod supports periods without end
        $cycle0 = new GlobalDatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), null);
        $cycle = new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), null);
        $this->assertEquals($cycle0->start, $cycle->start);
        $this->assertEquals($cycle0->interval, $cycle->interval);
        $this->assertEquals($cycle0->end, $cycle->end);
    }

    /**
     * Invalid DatePeriod initialization. Only start
     */
    public function testInvalidInitializationStart()
    {
        $this->expectException(Exception::class);
        new DatePeriod(new DateTime('2018-10-02 08:00:00Z'));
    }

    /**
     * Invalid DatePeriod initialization. Only interval
     */
    public function testInvalidInitializationInterval()
    {
        $this->expectException(Exception::class);
        new DatePeriod(null, new DateInterval('2018-10-02 08:00:00Z'));
    }

    /**
     * Invalid DatePeriod initialization. Invalid start
     */
    public function testInvalidInitializationInvalidStart()
    {
        $this->expectException(Exception::class);
        new DatePeriod('', new DateInterval('P1D'), [new DateTime('2018-10-02 08:00:00Z'), 0]);
    }

    /**
     * Invalid DatePeriod initialization. Invalid interval
     */
    public function testInvalidInitializationInvalidInterval()
    {
        $this->expectException(Exception::class);
        new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), '', [new DateTime('2018-10-02 08:00:00Z'), 0]);
    }

    /**
     * Invalid DatePeriod initialization. Invalid end
     */
    public function testInvalidInitializationInvalidEnd()
    {
        $this->expectException(Exception::class);
        new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), ['', 0]);
    }

    /**
     * Invalid DatePeriod initialization. Invalid recurrence
     */
    public function testInvalidInitializationInvalidRecurrence()
    {
        $this->expectException(Exception::class);
        new DatePeriod(new DateTime('2018-10-02 08:00:00Z'), new DateInterval('P1D'), [new DateTime('2018-10-02 08:00:00Z'), -2]);
    }
}
