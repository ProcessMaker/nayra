<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface;

/**
 * Repository for DiagramInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface DiagramRepositoryInterface extends RepositoryInterface
{

    /**
     * Create a diagram instance.
     *
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface
     */
    public function createDiagramInstance();

    /**
     * Load a diagram from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface
     */
    public function loadDiagramByUid($uid);

    /**
     * Create or update a diagram to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\DiagramInterface $diagram
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(DiagramInterface $diagram, $saveChildElements=false);
}
