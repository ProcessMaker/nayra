<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\Collaboration;
use ProcessMaker\Models\DataStoreCollection;
use ProcessMaker\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

class SignalEndEventTest extends EngineTestCase
{
    /**
     * Creates a process with a throwing signal and other with an end signal event
     *
     * @return array
     */
    public function createSignalStartEventProcesses()
    {
        $item = $this->rootElementRepository->createItemDefinitionInstance([
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String'
        ]);

        $signal = $this->rootElementRepository->createMessageInstance();
        $signal->setId('SignalA');
        $signal->setItem($item);

        //Process A
        $processA = $this->processRepository->createProcessInstance();
        $processA->setEngine($this->engine);
        $startA = $this->eventRepository->createStartEventInstance();
        $activityA1 = $this->activityRepository->createActivityInstance();
        $eventA = $this->eventRepository->createIntermediateThrowEventInstance();
        $signalEventDefA = $this->rootElementRepository->createSignalEventDefinitionInstance();
        $signalEventDefA->setId("signalEvent1");
        $signalEventDefA->setPayload($signal);
        $eventA->getEventDefinitions()->push($signalEventDefA);
        $activityA2 = $this->activityRepository->createActivityInstance();
        $endA = $this->eventRepository->createEndEventInstance();

        $startA->createFlowTo($activityA1, $this->flowRepository);
        $activityA1->createFlowTo($eventA, $this->flowRepository);
        $eventA->createFlowTo($activityA2, $this->flowRepository);
        $activityA2->createFlowTo($endA, $this->flowRepository);

        $processA->addActivity($activityA1)
            ->addActivity($activityA2)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->processRepository->createProcessInstance();
        $processB->setEngine($this->engine);

        $startB = $this->eventRepository->createStartEventInstance();
        $activityB1 = $this->activityRepository->createActivityInstance();
        $signalEventDefB= $this->rootElementRepository->createSignalEventDefinitionInstance();
        $signalEventDefB->setPayload($signal);

        $signalEndEventB = $this->eventRepository->createEndEventInstance();
        $signalEndEventB->getEventDefinitions()->push($signalEventDefB);

        $startB->createFlowTo($activityB1, $this->flowRepository);
        $activityB1->createFlowTo($signalEndEventB, $this->flowRepository);

        $processB->addActivity($activityB1)
            ->addEvent($startB)
            ->addEvent($signalEndEventB);

        $startA->nombre =  "StartA";
        $startB->nombre =  "StartB";
        $signalEndEventB->nombre = "Signal End Event B";
        $endA->nombre = "End Event A";

        return [$processA, $processB];
    }


    /**
     * Tests the signal end event of a process
     */
    public function testSignalEndEvent()
    {
        //Create two processes
        list($processA, $processB) = $this->createSignalStartEventProcesses();

        //Create a collaboration
        $collaboration = new Collaboration;

        //Add process A as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processA);
        $participant->setParticipantMultiplicity(1, 0);
        $collaboration->getParticipants()->push($participant);

        //Add process B as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processB);
        $participant->setParticipantMultiplicity(1, 0);
        $collaboration->getParticipants()->push($participant);

        //Create message flow from intermediate events A to B
        $eventA = $processA->getEvents()->item(1);
        $signalEndEventB = $processB->getEvents()->item(1);
        $messageFlow = $this->messageFlowRepository->createMessageFlowInstance();
        $messageFlow->setCollaboration($collaboration);
        $messageFlow->setSource($eventA);
        $messageFlow->setTarget($signalEndEventB);
        $collaboration->addMessageFlow($messageFlow);

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $dataStoreA = $this->dataStoreRepository->createDataStoreInstance();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->dataStoreRepository->createDataStoreInstance();
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
        ]);

        // we start the process A
        $startA = $processA->getEvents()->item(0);
        $activityA1 = $processA->getActivities()->item(0);

        $startA->start();
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        // we finish the first activity so that a new event should be created in the second process
        $tokenA = $activityA1->getTokens($instanceA)->item(0);
        $activityA1->complete($tokenA);
        $this->engine->runToNextState();

        //Assertion: The processA activity should be finished and a the instance of processB should end
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,

            //Events triggered when the catching event runs
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,

            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,

            //Next activity should be activated in the first process
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
