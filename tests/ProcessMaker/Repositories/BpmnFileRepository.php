<?php

namespace ProcessMaker\Repositories;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ProcessMaker\Models\ActivityRepository;
use ProcessMaker\Models\DataStoreRepository;
use ProcessMaker\Models\EventRepository;
use ProcessMaker\Models\ExecutionInstanceRepository;
use ProcessMaker\Models\FlowRepository;
use ProcessMaker\Models\GatewayRepository;
use ProcessMaker\Models\MessageFlowRepository;
use ProcessMaker\Models\ProcessRepository;
use ProcessMaker\Models\RootElementRepository;
use ProcessMaker\Models\TokenRepository;
use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ActivityRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ArtifactRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\DataStoreRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\DiagramRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\EventRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\FlowRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\GatewayRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ProcessRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\ShapeRepositoryInterface;
use ProcessMaker\Nayra\Contracts\Repositories\TokenRepositoryInterface;

/**
 * Description of BpmnFileRepository
 *
 */
class BpmnFileRepository extends DOMDocument implements RepositoryFactoryInterface
{
    const BPMN = 'http://www.omg.org/spec/BPMN/20100524/MODEL';

    private $bpmnElements = [];

    /**
     * @var EngineInterface
     */
    private $engine;

    /**
     * @var \ProcessMaker\Models\ExecutionInstanceRepository $executionInstanceRepository
     */
    private $executionInstanceRepository;

    /**
     *
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = null, $encoding = null)
    {
        parent::__construct($version, $encoding);
        $this->registerNodeClass(DOMElement::class, BpmnFileElement::class);
    }

    /**
     * Set the engine used.
     *
     * @param EngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Get the used engine.
     *
     * @return EngineInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Find a element by id.
     *
     * @param string $id
     *
     * @return BpmnFileElement
     */
    public function findElementById($id)
    {
        $xpath = new DOMXPath($this);
        $nodes = $xpath->query("//*[@id='$id']");
        return $nodes ? $nodes->item(0) : null;
    }

    /**
     * Set the BPMN instance by id.
     *
     * @param string $id
     * @param EntityInterface $bpmn
     */
    public function setBpmnElementById($id, $bpmn)
    {
        $this->bpmnElements[$id] = $bpmn;
    }

    /**
     * Load a BPMN element.
     *
     * @param string $id
     *
     * @return EntityInterface
     */
    public function loadBpmElementById($id)
    {
        $this->bpmnElements[$id] = isset($this->bpmnElements[$id])
            ? $this->bpmnElements[$id] : $this->findElementById($id)->getBpmn();
        return $this->bpmnElements[$id];
    }

    /**
     * Verify if the BPMN element identified by id was previously loaded.
     *
     * @param string $id
     *
     * @return boolean
     */
    public function hasBpmnInstance($id)
    {
        return isset($this->bpmnElements[$id]);
    }

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

    }

    /**
     * @return ArtifactRepositoryInterface
     */
    public function getArtifactRepository()
    {

    }

    /**
     * @return FlowRepositoryInterface
     */
    public function getFlowRepository()
    {
        return new FlowRepository($this);
    }

    /**
     * @return ShapeRepositoryInterface
     */
    public function getShapeRepository()
    {

    }

    /**
     * @return DataStoreRepositoryInterface
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
     * @return RootElementRepository
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

    /**
     * @return \ProcessMaker\Models\ExecutionInstanceRepository
     */
    public function getExecutionInstanceRepository()
    {
        if (empty($this->executionInstanceRepository)) {
            $this->executionInstanceRepository = new ExecutionInstanceRepository($this);
        }
        return $this->executionInstanceRepository;
    }
}
