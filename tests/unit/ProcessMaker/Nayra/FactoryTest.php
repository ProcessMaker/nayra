<?php

use PHPUnit\Framework\TestCase;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\StateInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

class FactoryTest extends TestCase
{
    public function testInstantiation ()
    {
        $config = $this->createMappingConfiguration();
        $factory = new \ProcessMaker\Nayra\Contracts\Factory($config);
        $activity = $factory->getInstanceOf(ActivityInterface::class);
        $this->assertNotNull($activity);

        $tokenPlace = $factory->getInstanceOf(StateInterface::class);
        $token = $factory->getInstanceOf(TokenInterface ::class, $tokenPlace);

        $this->assertEquals($token->getOwnerStatus(), $tokenPlace);
    }
}