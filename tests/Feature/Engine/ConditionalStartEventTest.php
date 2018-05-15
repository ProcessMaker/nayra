<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test a condition start event.
 *
 */
class ConditionalStartEventTest extends EngineTestCase
{

    /**
     * Test conditional start event
     *
     */
    public function testConditionalStartEvent()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->load(__DIR__ . '/files/Conditional_StartEvent.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->loadBpmElementById('Conditional_StartEvent');

        //Create a default environment data
        $environmentData = $bpmnRepository->getDataStoreRepository()->createDataStoreInstance();
        $this->engine->setDataStore($environmentData);

        //Get start event and event definition references
        $startEvent = $bpmnRepository->loadBpmElementById('StartEvent_1');
        $conditionalEvent = $bpmnRepository->loadBpmElementById('_ConditionalEventDefinition_2');

        //Trigger the start event
        $startEvent->execute($conditionalEvent);

        $this->assertEquals(0, $process->getInstances()->count());

        //Add the environmental data required by the condition
        $environmentData->putData('a', '1');

        //Trigger the start event
        $startEvent->execute($conditionalEvent);

        $this->assertEquals(1, $process->getInstances()->count());
    }
}
