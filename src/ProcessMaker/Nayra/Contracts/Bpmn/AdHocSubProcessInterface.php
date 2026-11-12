<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * BPMN Ad-Hoc SubProcess.
 */
interface AdHocSubProcessInterface extends SubProcessInterface
{
    const BPMN_PROPERTY_COMPLETION_CONDITION = 'completionCondition';

    const BPMN_PROPERTY_ORDERING = 'ordering';

    const ORDERING_PARALLEL = 'Parallel';

    const ORDERING_SEQUENTIAL = 'Sequential';

    /**
     * @return FormalExpressionInterface|null
     */
    public function getCompletionCondition();

    /**
     * @param FormalExpressionInterface|null $condition
     *
     * @return $this
     */
    public function setCompletionCondition(FormalExpressionInterface $condition = null);

    /**
     * @return string
     */
    public function getOrdering();

    /**
     * @param string $ordering
     *
     * @return $this
     */
    public function setOrdering($ordering);
}
