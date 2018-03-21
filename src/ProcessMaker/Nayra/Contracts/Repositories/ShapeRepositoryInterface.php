<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface;

/**
 * Repository for ShapeInterface
 *
 */
interface ShapeRepositoryInterface extends RepositoryInterface
{

    /**
     * Load a shape from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface
     */
    public function createShapeInstance();

    /**
     * Load a shape from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface
     */
    public function loadShapeByUid($uid);

    /**
     * Create or update a shape to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ShapeInterface $shape
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(ShapeInterface $shape, $saveChildElements=false);
}
