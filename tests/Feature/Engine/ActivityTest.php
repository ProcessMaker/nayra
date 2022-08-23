<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\Activity;
use ProcessMaker\Nayra\Contracts\Bpmn\BoundaryEventInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use Tests\Feature\Engine\EngineTestCase;

/**
 * Tests the activity collection
 */
class ActivityTest extends EngineTestCase
{
    /**
     * Test get boundary events from standalone activity
     */
    public function testGetEmptyBoundaryEvents()
    {
        $element = new Activity();
        $this->assertCount(0, $element->getBoundaryEvents());
    }

    /**
     * Test get boundary events from call activity with boundary events
     */
    public function testGetBoundaryEvents()
    {
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load(__DIR__ . '/files/Error_BoundaryEvent_CallActivity.bpmn');

        // Get boundary events of call activity _7
        $element = $bpmnRepository->getCallActivity('_7');
        $boundaryEvents = $element->getBoundaryEvents();

        // Assertion: There is one boundary event with id=_12
        $this->assertCount(1, $boundaryEvents);
        $boundaryEvent = $boundaryEvents->item(0);
        $this->assertInstanceOf(BoundaryEventInterface::class, $boundaryEvent);
        $this->assertEquals('_12', $boundaryEvent->getId());
    }
}
