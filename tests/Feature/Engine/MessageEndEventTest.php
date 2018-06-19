<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\DataStoreCollection;
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
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StartEventInterface;

/**
 * Test message end events
 */
class MessageEndEventTest extends EngineTestCase
{
    /**
     * Creates a process with a throwing message and other with an end message event
     *
     * @return array
     */
    public function createMessageStartEventProcesses()
    {
        $item = $this->factory->createItemDefinition([
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String'
        ]);

        $message = $this->factory->createMessage();
        $message->setId('MessageA');
        $message->setItem($item);

        $processA = $this->factory->createProcess();
        $processA->setEngine($this->engine);
        $processA->setFactory($this->factory);
        $startA = $this->factory->createStartEvent();
        $activityA1 = $this->factory->createActivity();
        $eventA = $this->factory->createIntermediateCatchEvent();
        $messageEventDefA = $this->factory->createMessageEventDefinition();
        $messageEventDefA->setId("MessageEvent1");
        $messageEventDefA->setPayload($message);
        $eventA->getEventDefinitions()->push($messageEventDefA);
        $activityA2 = $this->factory->createActivity();
        $endA = $this->factory->createEndEvent();

        $startA->createFlowTo($activityA1, $this->factory);
        $activityA1->createFlowTo($eventA, $this->factory);
        $eventA->createFlowTo($activityA2, $this->factory);
        $activityA2->createFlowTo($endA, $this->factory);

        $processA->addActivity($activityA1)
            ->addActivity($activityA2)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->factory->createProcess();
        $processB->setEngine($this->engine);

        $startB = $this->factory->createStartEvent();
        $activityB1 = $this->factory->createActivity();
        $messageEventDefB= $this->factory->createMessageEventDefinition();
        $messageEventDefB->setPayload($message);

        $messageEndEventB = $this->factory->createEndEvent();
        $messageEndEventB->getEventDefinitions()->push($messageEventDefB);

        $startB->createFlowTo($activityB1, $this->factory);
        $activityB1->createFlowTo($messageEndEventB, $this->factory);

        $processB->addActivity($activityB1)
            ->addEvent($startB)
            ->addEvent($messageEndEventB);

        return [$processA, $processB];
    }


    /**
     * Tests the message end event of a process
     */
    public function testMessageEndEvent()
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
        $messageEndEventB = $processB->getEvents()->item(1);
        $messageFlow = $this->factory->createMessageFlow();
        $messageFlow->setCollaboration($collaboration);
        $messageFlow->setSource($messageEndEventB);
        $messageFlow->setTarget($eventA);
        $collaboration->addMessageFlow($messageFlow);

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $dataStoreA = $this->factory->createDataStore();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->factory->createDataStore();
        $dataStoreB->putData('B', '1');

        $dataStoreCollectionA = new DataStoreCollection();
        $dataStoreCollectionA->add($dataStoreA);

        $dataStoreCollectionB = new DataStoreCollection();
        $dataStoreCollectionB->add($dataStoreB);

        $processA->setDataStores($dataStoreCollectionA);
        $processB->setDataStores($dataStoreCollectionB);

        $instanceA = $this->engine->createExecutionInstance($processA, $dataStoreA);
        $instanceB = $this->engine->createExecutionInstance($processB, $dataStoreB);

        // we start the second process and run it up to the end
        $startB = $processB->getEvents()->item(0);
        $activityB1 = $processB->getActivities()->item(0);

        // we start the process A
        $startA = $processA->getEvents()->item(0);
        $activityA1 = $processA->getActivities()->item(0);

        $startA->start();
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            //Instance for process A
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            //Instance for process B
            ProcessInterface::EVENT_PROCESS_INSTANCE_CREATED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // we finish the first activity so that the catch message is activated
        $tokenA = $activityA1->getTokens($instanceA)->item(0);
        $activityA1->complete($tokenA);
        $this->engine->runToNextState();

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES
        ]);

        $startB->start();
        $this->engine->runToNextState();

        //Assertion: Process B - The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenB = $activityB1->getTokens($instanceB)->item(0);
        $activityB1->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Process B - The activity is completed and the end event must be activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            // the throw token of the end is sent
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            MessageEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,

            // the Process A catching message is activated
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            // the Process B end throw event must consume its tokens
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_INSTANCE_COMPLETED,
        ]);
    }
}
