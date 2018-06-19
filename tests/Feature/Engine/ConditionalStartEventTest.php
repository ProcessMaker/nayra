<?php

namespace Tests\Feature\Engine;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;


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
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);

        $bpmnRepository->load(__DIR__ . '/files/Conditional_StartEvent.bpmn');

        //Load a process from a bpmn repository by Id
        $process = $bpmnRepository->getProcess('Conditional_StartEvent');

        //Create a default environment data
        $environmentData = $this->repository->createDataStore();
        $this->engine->setDataStore($environmentData);

        //Get start event and event definition references
        $startEvent = $bpmnRepository->getStartEvent('StartEvent_1');
        $conditionalEvent = $bpmnRepository->getConditionalEventDefinition('_ConditionalEventDefinition_2');

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
