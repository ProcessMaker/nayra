<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;

interface FormalExpressionInterface extends EntityInterface
{
    const BPMN_PROPERTY_LANGUAGE = 'language';

    const BPMN_PROPERTY_EVALUATES_TO_TYPE_REF = 'evaluatesToTypeRef';

    const BPMN_PROPERTY_BODY = 'body';

    /**
     * Get the expression language.
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Get the type that this Expression returns when evaluated.
     *
     * @return string
     */
    public function getEvaluatesToType();

    /**
     * Get the body of the Expression.
     *
     * @return string
     */
    public function getBody();
}
