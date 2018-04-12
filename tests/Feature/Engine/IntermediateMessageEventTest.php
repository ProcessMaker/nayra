<?php

namespace Tests\Feature\Engine;

/**
 * Test an activity with exception.
 *
 */
class IntermediateMessageEventTest extends EngineTestCase
{

    public function createMessageIntermediateEvent()
    {
        $item = $this->itemDefinitionRepository->createItemInstance();
        $message = $this->messageRepository->createMessageInstance();
        $signal = $this->signalRepository->createSignalInstance();
        $processA = $this->processRepository->createProcessInstance();
        $startA = $this->eventRepository->createStartEventInstance();
        $activityA = $this->activityRepository->createActivityInstance();
        $eventA = $this->eventRepository->createIntermediateThrowEventInstance();
        $messageEventDefA = $this->messageEventDefinitionRepository->createMessageEventInstance();
        $eventA->setMessage($messageEventDefA);
        $messageEventDefB->setMessage($message);
        $activityB = $this->activityRepository->createActivityInstance();
        $endA = $this->eventRepository->createStartEventInstance();

        $processB = $this->processRepository->createProcessInstance();
        $startB = $this->eventRepository->createStartEventInstance();
        $activityC = $this->activityRepository->createActivityInstance();
        $eventB = $this->eventRepository->createIntermediateCatchEventInstance();
        $messageEventDefB = $this->messageEventDefinitionRepository->createMessageEventDefinitionInstance();
        $messageEventDefB->setMessage($message);
        $eventB->setMessage($messageEventDefB);
        $activityD = $this->activityRepository->createActivityInstance();
        $endB = $this->eventRepository->createStartEventInstance();
    }

    public function testEvent()
    {
        $collaboration = new \ProcessMaker\Models\Collaboration;
        $participant = new Participant();
        $participant = $this->createProcessA();
        $collaboration->getParticipants()->add($participant);
        $participant = new Participant();
        $participant = $this->createProcessB();
        $collaboration->getParticipants()->add($participant);
    }

    public function createProcessB($event2)
    {
        $message = $this->factory->createMessageFlow();
        $message->setSource($event1);
        $message->setTarget($event2);
    }
}
