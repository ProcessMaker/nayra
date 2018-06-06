<?php
namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Model\InclusiveGateway;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TransitionInterface;
use Tests\Feature\Engine\EngineTestCase;

class DataStoreTest extends EngineTestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testDataStoreSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $dataStore = $this->factory->createInstanceOf(DataStoreInterface::class);
        $process = $this->factory->createInstanceOf(ProcessInterface::class);
        $process->setFactory($this->factory);
        $dummyActivity = $this->factory->createInstanceOf(ActivityInterface::class);
        $dummyActivity->setFactory($this->factory);
        $state = $this->factory->createInstanceOf(StateInterface::class, $dummyActivity,'');

        //set process and state object to the data store
        $dataStore->setOwnerProcess($process);
        $dataStore->setState($state);

        //Assertion: The set process must be equal to the created process
        $this->assertEquals($process, $dataStore->getOwnerProcess());

        //Assertion: the set state must be equal to the created store
        $this->assertEquals($state, $dataStore->getState());

        //Assertion: the data store should have a non initialized item subject
        $this->assertNull($dataStore->getItemSubject());
    }
}