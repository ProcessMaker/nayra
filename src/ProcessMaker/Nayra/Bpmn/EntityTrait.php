<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\ReflectionMethod;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ReflectionClass;

/**
 * EntityTrait
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait EntityTrait
{

    /**
     * Factory used to build this element.
     *
     * @var RepositoryFactoryInterface $factory
     */
    private $factory;

    /**
     * EntityTrait constructor.
     *
     * @param array ...$args
     */
    public function __construct(...$args)
    {
        $this->callInits($args);
    }

    /**
     * Call the initFunctions defined in traits.
     *
     * @param $args
     */
    private function callInits($args)
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            $name = $method->getName();
            if (substr($name, 0, 4) === 'init') {
                call_user_func_array([$this, $name], $args);
            }
        }
    }

    /**
     * Get the factory used to build this element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Set the factory used to build this element.
     *
     * @param RepositoryFactoryInterface $factory
     *
     * @return $this
     */
    public function setFactory(RepositoryFactoryInterface $factory)
    {
        $this->factory = $factory;
        return $this;
    }
}
