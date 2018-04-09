<?php

namespace ProcessMaker\Models;


use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Repositories\GatewayRepositoryInterface;

/**
 * Gateway Repository
 *
 * @package ProcessMaker\Models
 */
class GatewayRepository implements GatewayRepositoryInterface
{

    use RepositoryTrait;

    /**
     * Create a gateway inclusive instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function createInclusiveGatewayInstance()
    {
        return new InclusiveGateway($this->getFactory());
    }

    /**
     * Creates an exclusive gateway instance
     *
     * @return ExclusiveGateway
     */
    public function createExclusiveGatewayInstance()
    {
        return new ExclusiveGateway($this->getFactory());
    }

    /**
     * Create a gateway instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function createGatewayInstance()
    {
        // TODO: Implement createGatewayInstance() method.
    }

    /**
     * Load a gateway from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function loadGatewayByUid($uid)
    {

    }

    /**
     * Create or update a gateway to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface $gateway
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(GatewayInterface $gateway, $saveChildElements = false)
    {

    }

    /**
     * Create an instance of the entity.
     *
     * @param ProcessInterface|null $process
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function create(ProcessInterface $process = null)
    {

    }
}