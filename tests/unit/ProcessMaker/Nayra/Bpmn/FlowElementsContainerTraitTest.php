<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Activity;
use ProcessMaker\Nayra\Bpmn\Models\ExclusiveGateway;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Bpmn\Models\StartEvent;
use ProcessMaker\Nayra\Bpmn\Models\SubProcess;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementsContainerInterface;
use ProcessMaker\Test\Models\Repository;

/**
 * Cover FlowElementsContainerTrait helpers used by SubProcess.
 */
class FlowElementsContainerTraitTest extends TestCase
{
    public function testAddFlowElementClassifiesAndWiresOwnerProcess()
    {
        $process = new Process();
        $sub = new SubProcess();
        $sub->setOwnerProcess($process);

        $activity = new Activity();
        $activity->setId('A1');
        $event = new StartEvent();
        $event->setId('E1');
        $gateway = new ExclusiveGateway();
        $gateway->setId('G1');
        $other = new class implements FlowElementInterface {
            use FlowElementTrait;
        };
        $other->setId('X1');

        $sub->addFlowElement($activity);
        $sub->addFlowElement($event);
        $sub->addFlowElement($gateway);
        $sub->addFlowElement($other);

        $this->assertSame($sub, $activity->getContainer());
        $this->assertSame($process, $activity->getOwnerProcess());
        $this->assertSame($process, $activity->getProcess());
        $this->assertSame($process, $event->getOwnerProcess());
        $this->assertSame($process, $gateway->getOwnerProcess());

        $this->assertContains($activity, $sub->getActivities()->toArray());
        $this->assertContains($event, $sub->getEvents()->toArray());
        $this->assertContains($gateway, $sub->getGateways()->toArray());
        $this->assertContains($other, $sub->getArtifacts()->toArray());

        $flowElements = $sub->getFlowElements()->toArray();
        $this->assertContains($activity, $flowElements);
        $this->assertContains($event, $flowElements);
        $this->assertContains($gateway, $flowElements);
        $this->assertContains($other, $flowElements);
    }

    public function testAddFlowElementWithoutOwnerProcessStillClassifies()
    {
        $sub = new SubProcess();
        $activity = new Activity();
        $activity->setId('A2');
        $sub->addFlowElement($activity);

        $this->assertSame($sub, $activity->getContainer());
        $this->assertNull($activity->getOwnerProcess());
        $this->assertContains($activity, $sub->getActivities()->toArray());
    }

    public function testGetFlowElementsSkipsNonFlowElementArtifacts()
    {
        $sub = new SubProcess();
        $sub->getArtifacts()->push(new \stdClass());

        $this->assertCount(0, $sub->getFlowElements());
    }

    public function testFindStartEventsIgnoresEventTriggeredStarts()
    {
        $repo = new Repository();
        $sub = new SubProcess();

        $none = new StartEvent();
        $none->setId('NoneStart');
        $message = new StartEvent();
        $message->setId('MessageStart');
        $message->getEventDefinitions()->push($repo->createMessageEventDefinition());

        $sub->addFlowElement($none);
        $sub->addFlowElement($message);

        $starts = $sub->findStartEvents();
        $this->assertCount(1, $starts);
        $this->assertSame($none, $starts[0]);
    }

    public function testValidateDetectsWrongContainerAndLeavingFlow()
    {
        $repo = new Repository();
        $process = new Process();
        $process->setRepository($repo);

        $sub = new SubProcess();
        $sub->setId('Sub_1');
        $sub->setOwnerProcess($process);

        $start = new StartEvent();
        $start->setId('Start_1');
        $inner = new Activity();
        $inner->setId('Inner');
        $outside = new Activity();
        $outside->setId('Outside');
        $outside->setContainer($process);

        $sub->addFlowElement($start);
        $sub->addFlowElement($inner);
        $start->createFlowTo($inner, $repo);
        $inner->createFlowTo($outside, $repo);

        // Break container identity for the start event after wiring.
        $other = new SubProcess();
        $other->setId('Other');
        $start->setContainer($other);

        $errors = $sub->validate();
        $this->assertTrue($this->hasErrorMatching($errors, '/does not belong to subprocess/'));
        $this->assertTrue($this->hasErrorMatching($errors, '/leaves subprocess/'));
        $this->assertTrue($this->hasErrorMatching($errors, '/Start event .* does not belong/'));
    }

    public function testValidateDetectsCyclicContainment()
    {
        $a = new SubProcess();
        $a->setId('A');
        $b = new SubProcess();
        $b->setId('B');
        $a->getActivities()->push($b);
        $b->getActivities()->push($a);

        $errors = $a->validate();
        $this->assertTrue($this->hasErrorMatching($errors, '/cyclic parent relationship/'));
    }

    public function testValidateAllowsEventSubProcessWithoutNoneStart()
    {
        $repo = new Repository();
        $sub = new SubProcess();
        $sub->setId('EventSub');
        $sub->setTriggeredByEvent(true);

        $messageStart = new StartEvent();
        $messageStart->setId('MsgStart');
        $messageStart->getEventDefinitions()->push($repo->createMessageEventDefinition());
        $sub->addFlowElement($messageStart);

        $errors = $sub->validate();
        $this->assertFalse($this->hasErrorMatching($errors, '/requires at least one start event/'));
    }

    public function testHasCyclicContainmentUsesObjectHashWhenIdMissing()
    {
        $container = new class implements FlowElementsContainerInterface {
            use FlowElementsContainerTrait;
            use BaseTrait;
        };
        $nested = new class implements FlowElementsContainerInterface {
            use FlowElementsContainerTrait;
            use BaseTrait;
        };
        $container->getActivities()->push($nested);
        $nested->getActivities()->push($container);

        $method = new \ReflectionMethod(SubProcess::class, 'hasCyclicContainment');
        $method->setAccessible(true);
        $probe = new SubProcess();
        $this->assertTrue($method->invoke($probe, $container, []));
    }

    /**
     * @param string[] $errors
     * @param string $pattern
     *
     * @return bool
     */
    private function hasErrorMatching(array $errors, $pattern)
    {
        foreach ($errors as $error) {
            if (preg_match($pattern, $error)) {
                return true;
            }
        }

        return false;
    }
}
