<?php

namespace ProcessMaker\Nayra\Bpmn\Models;

use ProcessMaker\Nayra\Bpmn\ParticipantTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\ParticipantInterface;

/**
 * Activity implementation.
 *
 * @package ProcessMaker\Models
 */
class Participant implements ParticipantInterface
{

    use ParticipantTrait;
}
