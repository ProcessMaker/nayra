<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Bpmn\Models\Collaboration;
use ProcessMaker\Models\DataStoreCollection;
use ProcessMaker\Nayra\Bpmn\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EndEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\SignalEventDefinitionInterface;

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
        $eventA = $this->eventRepository->createIntermediateCatchEventInstance();
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
        $messageFlow->setSource($signalEndEventB);
        $messageFlow->setTarget($eventA);
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

        // we finish the first activity so that the catch signal is activated
        $tokenA = $activityA1->getTokens($instanceA)->item(0);
        $activityA1->complete($tokenA);
        $this->engine->runToNextState();

        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_ARRIVES
            ]);

        //Start the process B
        $startB->start();
        $this->engine->runToNextState();

        //Assertion: Process B - The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        //Complete the activity
        $tokenB = $activityB1->getTokens($instanceB)->item(0);
        $activityB1->complete($tokenB);
        $this->engine->runToNextState();

        //Assertion: Process B - The activity is completed and the end event must be activated
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,

            // the throw token of the end is sent
            EndEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            SignalEventDefinitionInterface::EVENT_THROW_EVENT_DEFINITION,

            // the Process A catching message is activated
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CATCH,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_CONSUMED,
            IntermediateCatchEventInterface::EVENT_CATCH_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
            EventInterface::EVENT_EVENT_TRIGGERED,

            // the Process B end throw event must consume its tokens
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            EventInterface::EVENT_EVENT_TRIGGERED,
            ProcessInterface::EVENT_PROCESS_COMPLETED,
        ]);
    }
}
