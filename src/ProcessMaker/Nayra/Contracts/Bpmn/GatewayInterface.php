<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

/**
 * Gateway used to control how the Process flows.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface GatewayInterface extends FlowNodeInterface
{

    const BPMN_PROPERTY_DEFAULT = 'default';

    /**
     * Events defined for Gateway
     */
    const EVENT_GATEWAY_TOKEN_ARRIVES = 'GatewayTokenArrives';
    const EVENT_GATEWAY_ACTIVATED = 'GatewayActivated';
    const EVENT_GATEWAY_EXCEPTION = 'GatewayException';
    const EVENT_GATEWAY_TOKEN_PASSED = 'GatewayTokenPassed';
    const EVENT_GATEWAY_TOKEN_CONSUMED = 'GatewayTokenConsumed';

    /**
     * Token states defined for Gateway
     */
    const TOKEN_STATE_INCOMING = 'INCOMING';
    const TOKEN_STATE_OUTGOING = 'OUTGOING';

    /**
     * Get Process of the gateway.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the gateway.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     * @param callable $condition
     * @param bool $isDefault
     * @param \ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface $flowRepository
     *
     * @return $this
     */
    public function createConditionedFlowTo(FlowNodeInterface $target, callable $condition, $isDefault, FlowRepositoryInterface $flowRepository);

}
