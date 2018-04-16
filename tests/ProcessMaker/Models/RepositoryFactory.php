<?php

namespace ProcessMaker\Models;


use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Bpmn\RepositoryTrait;
use ProcessMaker\Nayra\Contracts\Repositories\ActivityRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ArtifactRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\DiagramRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\EventRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\GatewayRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ProcessRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RouteRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ShapeRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TaskRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface;

/**
 * Repository Factory
 *
 * @package ProcessMaker\Models
 */
class RepositoryFactory implements RepositoryFactoryInterface
{

    use RepositoryTrait;

    /**
     * @return ProcessRepositoryInterface
     */
    public function getProcessRepository()
    {
        return new ProcessRepository($this);
    }

    /**
     * @return ActivityRepositoryInterface
     */
    public function getActivityRepository()
    {
        return new ActivityRepository($this);
    }

    /**
     * @return GatewayRepositoryInterface
     */
    public function getGatewayRepository()
    {
        return new GatewayRepository($this);
    }

    /**
     * @return EventRepositoryInterface
     */
    public function getEventRepository()
    {
        return new EventRepository($this);
    }

    /**
     * @return DiagramRepositoryInterface
     */
    public function getDiagramRepository()
    {
        // TODO: Implement getDiagramRepository() method.
    }

    /**
     * @return ArtifactRepositoryInterface
     */
    public function getArtifactRepository()
    {
        // TODO: Implement getArtifactRepository() method.
    }

    /**
     * @return FlowRepositoryInterface
     */
    public function getFlowRepository()
    {
        return new FlowRepository();
    }

    /**
     * @return ShapeRepositoryInterface
     */
    public function getShapeRepository()
    {
        // TODO: Implement getShapeRepository() method.
    }

    /**
     * @return TaskRepositoryInterface
     */
    public function getTaskRepository()
    {
        // TODO: Implement getTaskRepository() method.
    }

    /**
     * @return RouteRepositoryInterface
     */
    public function getRouteRepository()
    {
        // TODO: Implement getRouteRepository() method.
    }

    /**
     * @return ActivityRepositoryInterface
     */
    public function getDataStoreRepository()
    {
        return new DataStoreRepository($this);
    }

    /**
     * @return TokenRepositoryInterface
     */
    public function getTokenRepository()
    {
        return new TokenRepository($this);
    }

    /**
     *
     * @return \ProcessMaker\Models\RootElementRepository
     */
    public function getRootElementRepository()
    {
        return new RootElementRepository($this);
    }

    /**
     *
     * @return \ProcessMaker\Models\MessageFlowRepository
     */
    public function getMessageFlowRepository()
    {
        return new MessageFlowRepository($this);
    }
}