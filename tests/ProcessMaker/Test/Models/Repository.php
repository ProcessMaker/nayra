<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\FactoryTrait;
use ProcessMaker\Test\Models\FormalExpression;
use ProcessMaker\Test\Models\CallActivity;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Test\Models\TestOneClassWithEmptyConstructor;
use ProcessMaker\Test\Models\TestTwoClassWithArgumentsConstructor;

/**
 * Repository
 *
 * @package ProcessMaker\Test\Models
 */
class Repository implements RepositoryInterface
{

    use FactoryTrait;

    /**
     * Create instance of FormalExpression.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\FormalExpressionInterface
     */
    public function createFormalExpression()
    {
        return new FormalExpression();
    }

    /**
     * Create instance of CallActivity.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface
     */
    public function createCallActivity()
    {
        return new CallActivity();
    }

    public function createExecutionInstanceRepository(StorageInterface $factory)
    {
        return new ExecutionInstanceRepository($factory);
    }

    public function createTestOne()
    {
        return new TestOneClassWithEmptyConstructor();
    }

    public function createTestTwo($field1, $field2)
    {
        return new TestTwoClassWithArgumentsConstructor($field1, $field2);
    }
}
