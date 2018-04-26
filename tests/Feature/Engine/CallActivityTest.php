<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Repositories\BpmnFileRepository;

/**
 * Test transitions
 *
 */
class CallActivityTest extends EngineTestCase
{

    /**
     * Test a parallel gateway with two outgoing flows.
     *
     */
    public function testParallelGateway()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnFileRepository();
        $bpmnRepository->load(__DIR__ . '/files/CallActivity_Process.bpmn');

        //Load a process from a bpmn repository by Id
        $collaboration = $bpmnRepository->loadBpmElementById('collaboration');
        $process = $bpmnRepository->loadBpmElementById('CallActivity_Process');

        $a = $collaboration->getParticipants();

        var_dump($process->getProperty(\ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface::BPMN_PROPERTY_PARTICIPANT));
    }
}
