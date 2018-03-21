<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

/**
 * Gateway used to control how the Process flows.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface GatewayInterface extends EntityInterface, FlowNodeInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnGateway';

    const TYPE_ = '';
    const TYPE_EXCLUSIVE = 'EXCLUSIVE';
    const TYPE_INCLUSIVE = 'INCLUSIVE';
    const TYPE_PARALLEL = 'PARALLEL';
    const DIRECTION_UNSPECIFIED = 'UNSPECIFIED';
    const DIRECTION_DIVERGING = 'DIVERGING';
    const DIRECTION_CONVERGING = 'CONVERGING';
    const DIRECTION_MIXED = 'MIXED';
    const EVENT_GATEWAY_TYPE_NONE = 'NONE';
    const EVENT_GATEWAY_TYPE_PARALLEL = 'PARALLEL';
    const EVENT_GATEWAY_TYPE_EXCLUSIVE = 'EXCLUSIVE';

    /**
     * Events defined for Gateway
     */
    const EVENT_GATEWAY_TOKEN_ARRIVES = 'GatewayTokenArrives';
    const EVENT_GATEWAY_ACTIVATED = 'GatewayActivated';
    const EVENT_GATEWAY_EXCEPTION = 'GatewayException';
    const EVENT_GATEWAY_TOKEN_PASSED = 'GatewayTokenPassed';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'GAT_UID' => '',
        'GAT_NAME' => NULL,
        'GAT_TYPE' => '',
        'GAT_DIRECTION' => 'UNSPECIFIED',
        'GAT_INSTANTIATE' => '0',
        'GAT_EVENT_GATEWAY_TYPE' => 'NONE',
        'GAT_ACTIVATION_COUNT' => '0',
        'GAT_WAITING_FOR_START' => '1',
        'GAT_DEFAULT_FLOW' => ''
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    /**
     * Get Process of the gateway.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the gateway.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Create a flow to a target node.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface $target
     *
     * @return $this
     */
    public function createConditionedFlowTo(FlowNodeInterface $target, callable $condition, $isDefault, FlowRepositoryInterface $flowRepository);

}
