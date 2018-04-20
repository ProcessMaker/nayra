<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\Collaboration;
use ProcessMaker\Models\Message;
use ProcessMaker\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\EventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateThrowEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;

/**
 * Test an activity with exception.
 *
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
        $item = $this->rootElementRepository->createItemDefinitionInstance([
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String'
        ]);
        $message = $this->rootElementRepository->createMessageInstance();
            $message->setId('MessageA');
            $message->setItem($item);

        //Process A
        $processA = $this->processRepository->createProcessInstance();
        $startA = $this->eventRepository->createStartEventInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $eventA = $this->eventRepository->createIntermediateThrowEventInstance();
            $messageEventDefA = $this->rootElementRepository->createMessageEventDefinitionInstance();
            $messageEventDefA->setId("MessageEvent1");
                $messageEventDefA->setMessage($message);
            $eventA->getEventDefinitions()->push($messageEventDefA);
        $activityB = $this->activityRepository->createActivityInstance();
        $endA = $this->eventRepository->createEndEventInstance();

        $startA->createFlowTo($activityA, $this->flowRepository);
        $activityA->createFlowTo($eventA, $this->flowRepository);
        $eventA->createFlowTo($activityB, $this->flowRepository);
        $activityB->createFlowTo($endA, $this->flowRepository);

        $processA->addActivity($activityA)
            ->addActivity($activityB)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->processRepository->createProcessInstance();
        $startB = $this->eventRepository->createStartEventInstance();
        $activityC = $this->activityRepository->createActivityInstance();
        $eventB = $this->eventRepository->createIntermediateCatchEventInstance();
            $messageEventDefB = $this->rootElementRepository->createMessageEventDefinitionInstance();
                $messageEventDefB->setMessage($message);
            $eventB->getEventDefinitions()->push($messageEventDefB);
        $activityD = $this->activityRepository->createActivityInstance();
        $endB = $this->eventRepository->createEndEventInstance();

        $startB->createFlowTo($activityC, $this->flowRepository);
        $activityC->createFlowTo($eventB, $this->flowRepository);
        $eventB->createFlowTo($activityD, $this->flowRepository);
        $activityD->createFlowTo($endB, $this->flowRepository);

        $processB->addActivity($activityC)
            ->addActivity($activityD)
            ->addEvent($startB)
            ->addEvent($eventB)
            ->addEvent($endB);

        return [$processA, $processB];
    }

    public function createSignalIntermediateEventProcesses()
    {
        $signal = $this->signalRepository->createSignalInstance();

        //Process A
        $processA = $this->processRepository->createProcessInstance();
        $startA = $this->eventRepository->createStartEventInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $eventA = $this->eventRepository->createIntermediateThrowEventInstance();
            $signalEventDefA = $this->eventDefinitionRepository->createSignalEventInstance();
                $signalEventDefA->setSignal($signal);
            $eventA->setEventDefinition($signalEventDefA);
        $activityB = $this->activityRepository->createActivityInstance();
        $endA = $this->eventRepository->createEndEventInstance();



        $startA->createFlowTo($activityA, $this->flowRepository);
        $activityA->createFlowTo($eventA, $this->flowRepository);
        $eventA->createFlowTo($activityB, $this->flowRepository);
        $activityB->createFlowTo($endA, $this->flowRepository);

        $processA->addActivity($activityA)
            ->addActivity($activityB)
            ->addEvent($startA)
            ->addEvent($eventA)
            ->addEvent($endA);

        //Process B
        $processB = $this->processRepository->createProcessInstance();
        $startB = $this->eventRepository->createStartEventInstance();
        $activityC = $this->activityRepository->createActivityInstance();
        $eventB = $this->eventRepository->createIntermediateCatchEventInstance();
            $signalEventDefB = $this->eventDefinitionRepository->createSignalEventInstance();
                $signalEventDefB->setSignal($signal);
            $eventB->setEventDefinition($signalEventDefB);
        $activityD = $this->activityRepository->createActivityInstance();
        $endB = $this->eventRepository->createEndEventInstance();

        $startB->createFlowTo($activityC, $this->flowRepository);
        $activityC->createFlowTo($eventB, $this->flowRepository);
        $eventB->createFlowTo($activityD, $this->flowRepository);
        $activityD->createFlowTo($endB, $this->flowRepository);

        $processB->addActivity($activityC)
            ->addActivity($activityD)
            ->addEvent($startB)
            ->addEvent($eventB)
            ->addEvent($endB);

        return [$processA, $processB];
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
        $participant->setParticipantMultiplicity(1, 0);
        $collaboration->getParticipants()->push($participant);

        //Add process B as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processB);
        $participant->setParticipantMultiplicity(1, 0);
        $collaboration->getParticipants()->push($participant);

        //Create mmessage flow from intemediate events A to B
        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);
        $messageFlow = $this->messageFlowRepository->createMessageFlowInstance();
        $messageFlow->setCollaboration($collaboration);
        $messageFlow->setSource($eventA);
        $messageFlow->setTarget($eventB);
        $collaboration->addMessageFlow($messageFlow);

        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);

        $eventA->collaboration = $collaboration;
        $eventB->collaboration = $collaboration;

        $dataStoreA = $this->dataStoreRepository->createDataStoreInstance();
        $dataStoreA->putData('A', '1');

        $dataStoreB = $this->dataStoreRepository->createDataStoreInstance();
        $dataStoreB->putData('B', '1');

        $this->engine->createExecutionInstance($processA, $dataStoreA);
        $this->engine->createExecutionInstance($processB, $dataStoreB);

        $startC = $processB->getEvents()->item(0);
        $activityC = $processB->getActivities()->item(0);

        $startC->start();
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenC = $activityC->getTokens($dataStoreB)->item(0);
        $activityC->complete($tokenC);

        $this->engine->runToNextState();

        //Assertion:
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
        ]);

        $startA = $processA->getEvents()->item(0);
        $activityA = $processA->getActivities()->item(0);

        $startA->start();
        $this->engine->runToNextState();

        //Assertion: The activity must be activated
        $this->assertEvents([
            EventInterface::EVENT_EVENT_TRIGGERED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);

        $tokenA = $activityA->getTokens($dataStoreA)->item(0);
        $activityA->complete($tokenA);

        $this->engine->runToNextState();

        //Assertion:
        $this->assertEvents([
            ActivityInterface::EVENT_ACTIVITY_COMPLETED,
            ActivityInterface::EVENT_ACTIVITY_CLOSED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_ARRIVES,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,

            //events triggered when the catching event runs
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_CONSUMED,
            IntermediateThrowEventInterface::EVENT_THROW_TOKEN_PASSED,
            ActivityInterface::EVENT_ACTIVITY_ACTIVATED,
        ]);
    }
}
