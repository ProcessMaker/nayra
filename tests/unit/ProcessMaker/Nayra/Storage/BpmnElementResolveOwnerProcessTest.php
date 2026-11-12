<?php

namespace ProcessMaker\Nayra\Storage;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\Process;
use ProcessMaker\Nayra\Bpmn\Models\SubProcess;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowElementInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ReflectionMethod;

/**
 * Cover resolveOwnerProcess branches that import paths rarely hit.
 */
class BpmnElementResolveOwnerProcessTest extends TestCase
{
    /**
     * @param mixed $parentInstance
     *
     * @return ProcessInterface|null
     */
    private function resolve($parentInstance)
    {
        $document = new DOMDocument();
        $document->registerNodeClass(\DOMElement::class, BpmnElement::class);
        $element = $document->createElement('bpmn:task');
        $document->appendChild($element);

        $method = new ReflectionMethod(BpmnElement::class, 'resolveOwnerProcess');
        $method->setAccessible(true);

        return $method->invoke($element, $parentInstance);
    }

    public function testNullParentReturnsNull()
    {
        $this->assertNull($this->resolve(null));
    }

    public function testProcessParentIsReturned()
    {
        $process = new Process();
        $this->assertSame($process, $this->resolve($process));
    }

    public function testFlowElementWithOwnerProcessIsReturned()
    {
        $process = new Process();
        $sub = new SubProcess();
        $sub->setOwnerProcess($process);

        $this->assertSame($process, $this->resolve($sub));
    }

    public function testWalksContainerWhenOwnerProcessMissing()
    {
        $process = new Process();

        $outer = new SubProcess();
        $outer->setId('Outer');
        // No owner process yet; container chain points at the process via a stub.
        $bridge = new class($process) {
            private $process;

            public function __construct(ProcessInterface $process)
            {
                $this->process = $process;
            }

            public function getContainer()
            {
                return $this->process;
            }
        };

        $inner = new class($bridge) implements FlowElementInterface {
            use \ProcessMaker\Nayra\Bpmn\FlowElementTrait;

            private $bridge;

            public function __construct($bridge)
            {
                $this->bridge = $bridge;
            }

            public function getOwnerProcess()
            {
                return null;
            }

            public function getContainer()
            {
                return $this->bridge;
            }
        };

        $this->assertSame($process, $this->resolve($inner));
    }

    public function testWalksToFlowElementThatHasOwnerProcess()
    {
        $process = new Process();
        $outer = new SubProcess();
        $outer->setOwnerProcess($process);

        $inner = new class($outer) implements FlowElementInterface {
            use \ProcessMaker\Nayra\Bpmn\FlowElementTrait;

            private $outer;

            public function __construct($outer)
            {
                $this->outer = $outer;
            }

            public function getOwnerProcess()
            {
                return null;
            }

            public function getContainer()
            {
                return $this->outer;
            }
        };

        $this->assertSame($process, $this->resolve($inner));
    }

    public function testObjectWithoutContainerEndsWithNull()
    {
        $orphan = new class {
        };
        $this->assertNull($this->resolve($orphan));
    }

    public function testContainerChainEndingInNullReturnsNull()
    {
        $leaf = new class implements FlowElementInterface {
            use \ProcessMaker\Nayra\Bpmn\FlowElementTrait;

            public function getOwnerProcess()
            {
                return null;
            }

            public function getContainer()
            {
                return null;
            }
        };

        $this->assertNull($this->resolve($leaf));
    }
}
