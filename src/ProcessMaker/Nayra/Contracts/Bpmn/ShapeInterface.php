<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Shape related to an element of the process.
 */
interface ShapeInterface extends EntityInterface
{
    /**
     * Type of element.
     */
    const TYPE = 'bpmnShape';

    /**
     * Properties.
     */
    const PROPERTIES = [
        'BOU_UID' => '',
        'PRJ_UID' => '',
        'DIA_UID' => '',
        'ELEMENT_UID' => '',
        'BOU_ELEMENT' => '',
        'BOU_ELEMENT_TYPE' => '',
        'BOU_X' => '0',
        'BOU_Y' => '0',
        'BOU_WIDTH' => '0',
        'BOU_HEIGHT' => '0',
        'BOU_REL_POSITION' => '0',
        'BOU_SIZE_IDENTICAL' => '0',
        'BOU_CONTAINER' => '',
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [

    ];

    /**
     * Get Process of the shape.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the shape.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);

    /**
     * Get Diagram of the shape.
     *
     * @return DiagramInterface
     */
    public function getDiagram();

    /**
     * Get Diagram of the shape.
     *
     * @return DiagramInterface
     */
    public function setDiagram(DiagramInterface $diagram);
}
