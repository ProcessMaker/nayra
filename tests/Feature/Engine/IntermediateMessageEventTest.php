<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageFlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\OperationInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;

/**
 * Test an activity with exception.
 */
class IntermediateMessageEventTest extends EngineTestCase
{
    /**
     * Returns an array of processes that contains message events
     *
     * @return array
     */
    public function createMessageIntermediateEventProcesses()
    {
        $properties = [
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String',
        ];

        $item = $this->repository->createItemDefinition($properties);
        $item->setProperties($properties);

        $message = $this->repository->createMessage();
        $message->setId('MessageA');
        $message->setItem($item);

        //Process A
        $processA = $this->repository->createProcess();
        $processA->setEngine($this->engine);
        $processA->setRepository($this->repository);
        $startA = $this->repository->createStartEvent();
        $activityA = $this->repository->createActivity();
        $eventA = $this->repository->createIntermediateThrowEvent();
        $messageEventDefA = $this->repository->createMessageEventDefinition();
        $messageEventDefA->setId('MessageEvent1');
        $messageEventDefA->setPayload($message);
        $eventA->getEventDefinitions()->push($messageEventDefA);
        $activityB = $this->repository->createActivity();
        $endA = $this->repository->createEndEvent();

        $startA->createFlowTo($activityA, $this->repository);
        $activityA->createFlowTo($eventA, $this->repository);
        $eventA->createFlowTo($activityB, $this->repository);
        $activityB->createFlowTo($endA, $this->repository);

        $processA->addActivity($activityA)
            ->addActivity($activityB)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->repository->createProcess();
        $startB = $this->repository->createStartEvent();
        $activityC = $this->repository->createActivity();
        $eventB = $this->repository->createIntermediateCatchEvent();
        $messageEventDefB = $this->repository->createMessageEventDefinition();
        $messageEventDefB->setPayload($message);
        $eventB->getEventDefinitions()->push($messageEventDefB);
        $activityD = $this->repository->createActivity();
        $endB = $this->repository->createEndEvent();

        $startB->createFlowTo($activityC, $this->repository);
        $activityC->createFlowTo($eventB, $this->repository);
        $eventB->createFlowTo($activityD, $this->repository);
        $activityD->createFlowTo($endB, $this->repository);

        $processB->addActivity($activityC)
            ->addActivity($activityD)
            ->addEvent($startB)
            ->addEvent($eventB)
            ->addEvent($endB);

        return [$processA, $processB];
    }

    /**
     * Create signal intermediate event processes
     */
    public function createSignalIntermediateEventProcesses()
    {
        $signal = $this->repository->createSignal();
        $signal->setId('Signal1');
        $signal->setName('SignalName');

        //Process A
        $processA = $this->repository->createProcess();
        $startA = $this->repository->createStartEvent();
        $activityA = $this->repository->createActivity();
        $eventA = $this->repository->createIntermediateThrowEvent();
        $signalEventDefA = $this->repository->createSignalEventDefinition();
        $signalEventDefA->setId('signalEventDefA');
        $signalEventDefA->setPayload($signal);
        $eventA->getEventDefinitions()->push($signalEventDefA);
        $activityB = $this->repository->createActivity();
        $endA = $this->repository->createEndEvent();

        $startA->createFlowTo($activityA, $this->repository);
        $activityA->createFlowTo($eventA, $this->repository);
        $eventA->createFlowTo($activityB, $this->repository);
        $activityB->createFlowTo($endA, $this->repository);

        $processA->addActivity($activityA)
            ->addActivity($activityB)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->repository->createProcess();
        $startB = $this->repository->createStartEvent();
        $activityC = $this->repository->createActivity();
        $eventB = $this->repository->createIntermediateCatchEvent();
        $signalEventDefB = $this->repository->createSignalEventDefinition();
        $signalEventDefB->setId('signalEventDefB');
        $signalEventDefB->setPayload($signal);
        $eventB->getEventDefinitions()->push($signalEventDefB);
        $activityD = $this->repository->createActivity();
        $endB = $this->repository->createEndEvent();

        $startB->createFlowTo($activityC, $this->repository);
        $activityC->createFlowTo($eventB, $this->repository);
        $eventB->createFlowTo($activityD, $this->repository);
        $activityD->createFlowTo($endB, $this->repository);

        $processB->addActivity($activityC)
            ->addActivity($activityD)
            ->addEvent($startB)
            ->addEvent($eventB)
            ->addEvent($endB);

        return [$processA, $processB];
    }

    /**
     * Test Process Definitions for intermediate messages
     */
    public function testProcessDefinitionForIntermediateMessages()
    {
        list($processA, $processB) = $this->createMessageIntermediateEventProcesses();
        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventDefA = $eventA->getEventDefinitions()->item(0);
        $eventDefB = $eventB->getEventDefinitions()->item(0);

        $message = $eventDefA->getPayload();
        $signal = $eventDefB->getPayload();

        //Assertion: Validate message element
        $this->assertNotNull($message, 'Event Definition A should have a message');
        $this->assertEquals('item', $message->getItem()->getId());
        $this->assertEquals(true, $message->getItem()->isCollection());
        $this->assertEquals(ItemDefinitionInterface::ITEM_KIND_INFORMATION, $message->getItem()->getItemKind());
        $this->assertEquals('String', $message->getItem()->getStructure());
        $this->assertNotNull($signal, 'Event Definition B should have a signal');

        $operation = $this->repository->createOperation();

        $eventDefA->setOperation($operation);

        $this->assertEquals($operation, $eventDefA->getOperation(),
            'The Event Definition Operation must be equal to the assigned in the test.');
    }

    /**
     * Test process definitions for intermediate signals
     */
    public function testProcessDefinitionForIntermediateSignals()
    {
        list($processA, $processB) = $this->createSignalIntermediateEventProcesses();
        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventDefA = $eventA->getEventDefinitions()->item(0);
        $eventDefB = $eventB->getEventDefinitions()->item(0);

        $message = $eventDefA->getPayload();
        $signal = $eventDefB->getPayload();

        $this->assertNotNull($message, 'Event Definition A should have a message');
        $this->assertNotNull($signal, 'Event Definition B should have a signal');
    }

    /**
     * Tests that message events are working correctly
     */
    public function testIntermediateEvent()
    {
        //Create two processes
        list($processA, $processB) = $this->createMessageIntermediateEventProcesses();

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

        //Create mmessage flow from intemediate events A to B
        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);
        $messageFlow = $this->repository->createMessageFlow();
        $messageFlow->setCollaboration($collaboration);
        $messageFlow->setSource($eventA);
        $messageFlow->setTarget($eventB);
        $collaboration->addMessageFlow($messageFlow);

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventA->collaboration = $collaboration;
        $eventB->collaboration = $collaboration;

        $dataStoreA = $this->repository->createDataStore();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->repository->createDataStore();
        $dataStoreB->putData('B', '1');

        $this->engine->loadCollaboration($collaboration);
        $instanceA = $this->engine->createExecutionInstance($processA, $dataStoreA);
        $instanceB = $this->engine->createExecutionInstance($processB, $dataStoreB);

        $startC = $processB->getEvents()->item(0);
        $activityC = $processB->getActivities()->item(0);

        $startC->start($instanceB);
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            //Instance of process A
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            //Instance of process B
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenC = $activityC->getTokens($instanceB)->item(0);
        $activityC->complete($tokenC);

        $this->engine->runToNextState();

        //Assertion:
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
        ]);

        $startA = $processA->getEvents()->item(0);
        $activityA = $processA->getActivities()->item(0);

        $startA->start($instanceA);
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenA = $activityA->getTokens($instanceA)->item(0);
        $activityA->complete($tokenA);

        $this->engine->runToNextState();

        //Assertion: The throwing process must advances to activity B an the catching process to activity D
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,

            //events triggered when the catching event runs
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }

    /**
     * Tests that the signal event works correctly
     */
    public function testSignalEvent()
    {
        //Create two processes
        list($processA, $processB) = $this->createSignalIntermediateEventProcesses();

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

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventA->collaboration = $collaboration;
        $eventB->collaboration = $collaboration;

        $dataStoreA = $this->repository->createDataStore();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->repository->createDataStore();
        $dataStoreB->putData('B', '1');

        $instanceA = $this->engine->createExecutionInstance($processA, $dataStoreA);
        $instanceB = $this->engine->createExecutionInstance($processB, $dataStoreB);

        $startC = $processB->getEvents()->item(0);
        $activityC = $processB->getActivities()->item(0);

        $startC->start($instanceB);
        $this->engine->runToNextState();

        //Assertion: The first activity of the second flow must be activated
        $this->assertEvents([
            //Instance for process A
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            //Instance for process B
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenC = $activityC->getTokens($instanceB)->item(0);
        $activityC->complete($tokenC);

        $this->engine->runToNextState();

        //Assertion: the second flows is stoppen in the catching event
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES,
        ]);

        $startA = $processA->getEvents()->item(0);
        $activityA = $processA->getActivities()->item(0);

        $startA->start($instanceA);
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenA = $activityA->getTokens($instanceA)->item(0);
        $activityA->complete($tokenA);

        $this->engine->runToNextState();

        //Assertion: The throwing process must advances to activity B an the catching process to activity D
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CATCH,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,

            //events triggered when the catching event runs
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_MESSAGE_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
