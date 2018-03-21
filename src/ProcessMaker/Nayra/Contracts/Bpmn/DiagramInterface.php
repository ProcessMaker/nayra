<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

/**
 * Diagram contains flow elements of a process.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface DiagramInterface extends EntityInterface
{

    /**
     * Type of element.
     */
    const TYPE = 'bpmnDiagram';


    /**
     * Properties.
     */
    const PROPERTIES = [
        'DIA_UID' => '',
        'PRJ_UID' => NULL,
        'DIA_NAME' => NULL,
        'DIA_IS_CLOSABLE' => '0'
    ];

    /**
     * Child elements.
     */
    const ELEMENTS = [
        'shapes' => ShapeInterface::TYPE,
    ];


    /**
     * Get Shapes of the diagram.
     *
     * @return ShapeCollectionInterface
     */
    public function getShapes();

    /**
     * Get Shapes of the diagram.
     *
     * @return ShapeCollectionInterface
     */
    public function setShapes(ShapeCollectionInterface $shapes);

    /**
     * Get Process of the diagram.
     *
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get Process of the diagram.
     *
     * @return ProcessInterface
     */
    public function setProcess(ProcessInterface $process);


}
