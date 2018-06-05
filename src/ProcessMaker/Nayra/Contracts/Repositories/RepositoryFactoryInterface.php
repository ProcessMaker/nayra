<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

/**
 * RepositoryFactory
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface RepositoryFactoryInterface
{

    /**
     * @return ProcessRepositoryInterface
     */
    public function getProcessRepository();

    /**
     * @return ActivityRepositoryInterface
     */
    public function getActivityRepository();

    /**
     * @return GatewayRepositoryInterface
     */
    public function getGatewayRepository();

    /**
     * @return EventRepositoryInterface
     */
    public function getEventRepository();

    /**
     * @return DiagramRepositoryInterface
     */
    public function getDiagramRepository();

    /**
     * @return ArtifactRepositoryInterface
     */
    public function getArtifactRepository();

    /**
     * @return FlowRepositoryInterface
     */
    public function getFlowRepository();

    /**
     * @return ShapeRepositoryInterface
     */
    public function getShapeRepository();

    /**
     * @return DataStoreRepositoryInterface
     */
    public function getDataStoreRepository();

    /**
     * @return TokenRepositoryInterface
     */
    public function getTokenRepository();

    /**
     * @return \ProcessMaker\Nayra\Contracts\Repositories\ExecutionInstanceRepositoryInterface
     */
    public function getExecutionInstanceRepository();
}
