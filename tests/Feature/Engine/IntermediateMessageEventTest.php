<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Models\Collaboration;
use ProcessMaker\Models\Participant;
use ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface;

/**
 * Test an activity with exception.
 *
 */
class IntermediateMessageEventTest extends EngineTestCase
{

    public function createMessageIntermediateEventProcesses()
    {
        $item = $this->rootElementRepository->createItemDefinitionInstance([
            'id' => 'item',
            'isCollection' => true,
            'itemKind' => ItemDefinitionInterface::ITEM_KIND_INFORMATION,
            'structure' => 'String'
        ]);
        $message = $this->rootElementRepository->createMessageInstance();
            $message->setItem($item);

        //Process A
        $processA = $this->processRepository->createProcessInstance();
        $startA = $this->eventRepository->createStartEventInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $eventA = $this->eventRepository->createIntermediateThrowEventInstance();
            $messageEventDefA = $this->rootElementRepository->createMessageEventDefinitionInstance();
                $messageEventDefA->setMessage($message);
            $eventA->getEventDefinitions()->push($messageEventDefA);
        $activityB = $this->activityRepository->createActivityInstance();
        $endA = $this->eventRepository->createStartEventInstance();

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
        $endB = $this->eventRepository->createStartEventInstance();

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
        $endA = $this->eventRepository->createStartEventInstance();

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
        $endB = $this->eventRepository->createStartEventInstance();

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

    public function testEvent()
    {
        //Create two processes
        list($processA, $processB) = $this->createMessageIntermediateEventProcesses();

        //Create a collaboration
        $collaboration = new Collaboration;

        //Add process A as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processA);
        $collaboration->getParticipants()->push($participant);

        //Add process B as participant of the collaboration
        $participant = new Participant();
        $participant->setProcess($processB);
        $collaboration->getParticipants()->push($participant);

        //Create mmessage flow from intemediate events A to B
        $eventA = $processA->getEvents()->item(1);
        $eventB = $processB->getEvents()->item(1);
        $messageFlow = $this->messageFlowRepository->createMessageFlowInstance();
        $messageFlow->setSource($eventA);
        $messageFlow->setTarget($eventB);
        $collaboration->getMessageFlows()->push($messageFlow);
        $messageFlow->getSource();
    }
}
