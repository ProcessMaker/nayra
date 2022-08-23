<?php

namespace ProcessMaker\Test\Models;

use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ProcessMaker\Nayra\RepositoryTrait;
use ProcessMaker\Test\Models\CallActivity;
use ProcessMaker\Test\Models\ExecutionInstance;
use ProcessMaker\Test\Models\FormalExpression;
use ProcessMaker\Test\Models\TestOneClassWithEmptyConstructor;
use ProcessMaker\Test\Models\TestTwoClassWithArgumentsConstructor;

/**
 * Repository
 */
class Repository implements RepositoryInterface
{
    use RepositoryTrait;

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

    /**
     * Create a execution instance repository.
     *
     * @return \ProcessMaker\Test\Models\ExecutionInstanceRepository
     */
    public function createExecutionInstanceRepository()
    {
        return new ExecutionInstanceRepository();
    }

    /**
     * Create a test class
     *
     * @return TestOneClassWithEmptyConstructor
     */
    public function createTestOne()
    {
        return new TestOneClassWithEmptyConstructor();
    }

    /**
     * Create a test class with parameters
     *
     * @param mixed $field1
     * @param mixed $field2
     *
     * @return TestTwoClassWithArgumentsConstructor
     */
    public function createTestTwo($field1, $field2)
    {
        return new TestTwoClassWithArgumentsConstructor($field1, $field2);
    }
}
