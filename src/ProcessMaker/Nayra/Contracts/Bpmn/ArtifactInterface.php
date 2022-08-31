<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Artifact interface.
 */
interface ArtifactInterface extends EntityInterface
{
    /**
     * Type of element.
     */
    const TYPE = 'bpmnArtifact';

    const TYPE_HORIZONTAL_LINE = 'HORIZONTAL_LINE';

    const TYPE_VERTICAL_LINE = 'VERTICAL_LINE';

    const TYPE_TEXT_ANNOTATION = 'TEXT_ANNOTATION';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'ART_UID' => '',
        'PRO_ID' => null,
        'ART_TYPE' => null,
        'ART_NAME' => null,
        'ART_CATEGORY_REF' => null,
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    /**
     * Get Process of the artifact.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the artifact.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);
}
