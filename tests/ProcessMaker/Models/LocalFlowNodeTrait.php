<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\FlowNodeTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\FlowNodeInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\GatewayInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;

/**
 * Trait to store flows as local variables.
 *
 * @package ProcessMaker\Models
 */
trait LocalFlowNodeTrait
{

    use FlowNodeTrait;

}
