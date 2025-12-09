<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Bpmn\Models\MessageEventDefinition;
use ProcessMaker\Nayra\Bpmn\Models\Message;
use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Bpmn\EventDefinitionInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Engine\ExecutionInstanceInterface;

/**
 * Tests for the MessageEventDefinition class
 */
class MessageEventDefinitionTest extends TestCase
{
    /**
     * Test that execute method handles null token gracefully
     * 
     * This test ensures that when execute is called with a null token
     * (which can happen in message start events), the method doesn't
     * throw an error and returns successfully.
     */
    public function testExecuteWithNullToken()
    {
        $messageEventDef = new MessageEventDefinition();
        $message = new Message();
        $messageEventDef->setPayload($message);

        // Create a mock event definition
        $event = $this->createMock(EventDefinitionInterface::class);

        // Create a mock target (catch event)
        $target = $this->createMock(FlowNodeInterface::class);

        // Create a mock instance
        $instance = $this->createMock(ExecutionInstanceInterface::class);

        // Execute with null token - should not throw an error
        $result = $messageEventDef->execute($event, $target, $instance, null);

        // Assert that the method returns the instance
        $this->assertSame($messageEventDef, $result);
    }

    /**
     * Test that execute method works correctly with a valid token
     */
    public function testExecuteWithValidToken()
    {
        $messageEventDef = new MessageEventDefinition();
        $message = new Message();
        $messageEventDef->setPayload($message);

        // Create a mock event definition
        $event = $this->createMock(EventDefinitionInterface::class);

        // Create a mock instance
        $instance = $this->createMock(ExecutionInstanceInterface::class);

        // Create a mock token
        $token = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface::class);

        // Mock getOwnerElement to return a throw event
        $throwEvent = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\ThrowEventInterface::class);
        $token->expects($this->once())
            ->method('getOwnerElement')
            ->willReturn($throwEvent);

        // Mock getInstance to return an instance
        $tokenInstance = $this->createMock(ExecutionInstanceInterface::class);
        $token->expects($this->any())
            ->method('getInstance')
            ->willReturn($tokenInstance);

        // Mock throw event methods
        $throwEvent->expects($this->once())
            ->method('getDataInputAssociations')
            ->willReturn(new Collection());

        // Mock target as catch event
        $catchEvent = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\CatchEventInterface::class);
        $catchEvent->expects($this->once())
            ->method('getDataOutputAssociations')
            ->willReturn(new Collection());

        // Mock instance data store
        $dataStore = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface::class);
        $dataStore->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $instance->expects($this->any())
            ->method('getDataStore')
            ->willReturn($dataStore);

        // Mock token instance data store
        $tokenDataStore = $this->createMock(\ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface::class);
        $tokenDataStore->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $tokenInstance->expects($this->any())
            ->method('getDataStore')
            ->willReturn($tokenDataStore);

        // Execute with valid token
        $result = $messageEventDef->execute($event, $catchEvent, $instance, $token);

        // Assert that the method returns the instance
        $this->assertSame($messageEventDef, $result);
    }
}
