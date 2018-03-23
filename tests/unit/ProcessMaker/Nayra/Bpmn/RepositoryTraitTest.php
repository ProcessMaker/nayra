<?php

namespace ProcessMaker\Nayra\Bpmn;

use PHPUnit\Framework\TestCase;
use ProcessMaker\Models\ActivityRepository;
use ProcessMaker\Models\RepositoryFactory;

class RepositoryTraitTest extends TestCase
{
    /**
     * Tests that a factory is correctly set in a repository trait
     */
    public function testSetFactory()
    {
        $factory = new RepositoryFactory();

        // ActivityRepository "inherits" from the repository trait
        $activity = new ActivityRepository();

        $activity->setFactory($factory);
        $this->assertEquals($activity->getFactory(), $factory);
    }
}
