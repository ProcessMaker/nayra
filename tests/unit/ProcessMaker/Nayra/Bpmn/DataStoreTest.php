<?php
namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use Tests\Feature\Engine\EngineTestCase;

/**
 * Tests for the DataStore class
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
class DataStoreTest extends EngineTestCase
{
    /**
     * Tests that setters and getters are working properly
     */
    public function testDataStoreSettersAndGetters()
    {
        // Create the objects that will be set in the data store
        $dataStore = $this->factory->createDataStore();
        $process = $this->factory->createProcess();
        $process->setFactory($this->factory);
        $dummyActivity = $this->factory->createActivity();
        $dummyActivity->setFactory($this->factory);
        $state = $this->factory->createState($dummyActivity,'');

        // Set process and state object to the data store
        $dataStore->setOwnerProcess($process);

        //Assertion: The get process must be equal to the set process
        $this->assertEquals($process, $dataStore->getOwnerProcess());

        //Assertion: the data store should have a non initialized item subject
        $this->assertNull($dataStore->getItemSubject());
    }
}
