<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\DataStoreCollection;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Test the message start event
 */
class MessageStartEventTest extends EngineTestCase
{
    /**
     * Returns an array of processes where the second process has a message start event
     *
     * @return array
     */
    public function createMessageStartEventProcesses()
    {
        $item = $this->repository->createItemDefinition([
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String'
        ]);

        $message = $this->repository->createMessage();
        $message->setId('MessageA');
        $message->setItem($item);

        //Process A
        $processA = $this->repository->createProcess();
        $processA->setEngine($this->engine);
        $processA->setRepository($this->repository);
        $startA = $this->repository->createStartEvent();
        $activityA1 = $this->repository->createActivity();
        $eventA = $this->repository->createIntermediateThrowEvent();
        $messageEventDefA = $this->repository->createMessageEventDefinition();
        $messageEventDefA->setId("MessageEvent1");
        $messageEventDefA->setPayload($message);
        $eventA->getEventDefinitions()->push($messageEventDefA);
        $activityA2 = $this->repository->createActivity();
        $endA = $this->repository->createEndEvent();

        $startA->createFlowTo($activityA1, $this->repository);
        $activityA1->createFlowTo($eventA, $this->repository);
        $eventA->createFlowTo($activityA2, $this->repository);
        $activityA2->createFlowTo($endA, $this->repository);

        $processA->addActivity($activityA1)
            ->addActivity($activityA2)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->repository->createProcess();
        $processB->setEngine($this->engine);
        $processB->setRepository($this->repository);

        $activityB1 = $this->repository->createActivity();
        $messageEventDefB = $this->repository->createMessageEventDefinition();
        $messageEventDefB->setPayload($message);

        $messageStartEventB = $this->repository->createStartEvent();
        $messageStartEventB->getEventDefinitions()->push($messageEventDefB);

        $endB = $this->repository->createEndEvent();

        $messageStartEventB->createFlowTo($activityB1, $this->repository);
        $activityB1->createFlowTo($endB, $this->repository);

        $processB->addActivity($activityB1)
            ->addEvent($messageStartEventB)
            ->addEvent($endB);

        return [$processA, $processB];
    }

    /**
     * Tests the start of a process when it receives a message
     */
    public function testMessageStartEvent()
    {
        //Create two processes
        list($processA, $processB) = $this->createMessageStartEventProcesses();

        //Create a collaboration
        $collaboration = new Collaboration;

        //Add process A as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processA);
        $participant->setParticipantMultiplicity(['maximum' => 1, 'minimum' => 0]);
        $collaboration->getParticipants()->push($participant);

        //Add process B as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processB);
        $participant->setParticipantMultiplicity(['maximum' => 1, 'minimum' => 0]);
        $collaboration->getParticipants()->push($participant);

        //Create message flow from intermediate events A to B
        $eventA = $processA->getEvents()->item(1);
        $messageStartEventB = $processB->getEvents()->item(0);
        $messageFlow = $this->repository->createMessageFlow();
        $messageFlow->setCollaboration($collaboration);
        $messageFlow->setSource($eventA);
        $messageFlow->setTarget($messageStartEventB);
        $collaboration->addMessageFlow($messageFlow);

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventA->collaboration = $collaboration;
        $eventB->collaboration = $collaboration;

        $dataStoreA = $this->repository->createDataStore();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->repository->createDataStore();
        $dataStoreB->putData('B', '1');

        $dataStoreCollectionA = new DataStoreCollection();
        $dataStoreCollectionA->add($dataStoreA);

        $dataStoreCollectionB = new DataStoreCollection();
        $dataStoreCollectionB->add($dataStoreB);

        $processA->setDataStores($dataStoreCollectionA);
        $processB->setDataStores($dataStoreCollectionB);

        $this->engine->loadCollaboration($collaboration);
        $this->engine->loadProcess($processB);
        $instanceA = $this->engine->createExecutionInstance($processA, $dataStoreA);

        // we start the process A
        $startA = $processA->getEvents()->item(0);
        $activityA1 = $processA->getActivities()->item(0);

        $startA->start($instanceA);
        $this->engine->runToNextState();

        //Assertion: messageFlow resources set and get should be equal
        $this->assertEquals($messageFlow->getSource(), $eventA);

        //Assertion: The activity must be activated
        $this->assertEvents([
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // we finish the first activity so that a new event should be created in the second process
        $tokenA = $activityA1->getTokens($instanceA)->item(0);
        $activityA1->complete($tokenA);
        $this->engine->runToNextState();

        //Assertion: The process1 activity should be finished and a new instance of the second process must be created
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,

            //events triggered when the catching event runs
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,

            //Actibity activated in the first process
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            //It must be triggered the start event of the second process
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
