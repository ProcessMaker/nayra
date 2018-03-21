<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;

/**
 * Repository for GatewayInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface GatewayRepositoryInterface extends RepositoryInterface
{

    /**
     * Create a gateway instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function createGatewayInstance();

    /**
     * Load a gateway from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface
     */
    public function loadGatewayByUid($uid);

    /**
     * Create or update a gateway to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface $gateway
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(GatewayInterface $gateway, $saveChildElements=false);
}
