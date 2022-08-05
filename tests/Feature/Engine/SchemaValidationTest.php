<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Storage\BpmnDocument;

/**
 * Test a terminate event.
 */
class SchemaValidationTest extends EngineTestCase
{
    /**
     * Test terminate end event
     */
    public function testValidDefinitions()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        libxml_use_internal_errors(true);
        $bpmnRepository->load(__DIR__.'/files/Lanes.bpmn');
        $validation = $bpmnRepository->validateBPMNSchema(__DIR__.'/xsd/BPMN20.xsd');
        $this->assertTrue($validation);
        $this->assertEmpty($bpmnRepository->getValidationErrors());
    }

    /**
     * Test terminate end event
     */
    public function testInvalidDefinitions()
    {
        //Load a BpmnFile Repository
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        libxml_use_internal_errors(true);
        $bpmnRepository->loadXML('Invalid BPMN');
        $validation = $bpmnRepository->validateBPMNSchema(__DIR__.'/xsd/BPMN20.xsd');
        $this->assertFalse($validation);
        $this->assertNotEmpty($bpmnRepository->getValidationErrors());
    }
}
