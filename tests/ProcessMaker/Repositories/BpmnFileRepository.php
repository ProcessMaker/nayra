<?php

namespace ProcessMaker\Repositories;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ProcessMaker\Nayra\Contracts\Engine\EngineInterface;
use ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface;

/**
 * Description of BpmnFileRepository
 *
 */
class BpmnFileRepository extends DOMDocument implements RepositoryFactoryInterface
{
    const BPMN = 'http://www.omg.org/spec/BPMN/20100524/MODEL';

    /**
     *
     * @var DOMXPath $xpath
     */
    private $xpath;

    private $bpmnElements = [];

    /**
     * @var \ProcessMaker\Nayra\Contracts\Engine\EngineInterface
     */
    private $engine;

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

    public function setEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
        return $this;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function findElementById($id)
    {
        $xpath = new DOMXPath($this);
        $nodes = $xpath->query("//*[@id='$id']");
        return $nodes ? $nodes->item(0) : null;
    }

    public function setBpmnElementById($id, $bpmn)
    {
        $this->bpmnElements[$id] = $bpmn;
    }

    public function loadBpmElementById($id)
    {
        $this->bpmnElements[$id] = isset($this->bpmnElements[$id])
            ? $this->bpmnElements[$id] : $this->findElementById($id)->getBpmn();
        return $this->bpmnElements[$id];
    }

    public function hasBpmnInstance($id)
    {
        return isset($this->bpmnElements[$id]);
    }

    /**
     * @return ProcessRepositoryInterface
     */
    public function getProcessRepository()
    {
        return new \ProcessMaker\Models\ProcessRepository($this);
    }

    /**
     * @return ActivityRepositoryInterface
     */
    public function getActivityRepository()
    {
        return new \ProcessMaker\Models\ActivityRepository($this);
    }

    /**
     * @return GatewayRepositoryInterface
     */
    public function getGatewayRepository()
    {
        return new \ProcessMaker\Models\GatewayRepository($this);
    }

    /**
     * @return EventRepositoryInterface
     */
    public function getEventRepository()
    {
        return new \ProcessMaker\Models\EventRepository($this);
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
        return new \ProcessMaker\Models\FlowRepository($this);
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
        return new \ProcessMaker\Models\DataStoreRepository($this);
    }

    /**
     * @return TokenRepositoryInterface
     */
    public function getTokenRepository()
    {
        return new \ProcessMaker\Models\TokenRepository($this);
    }

    /**
     *
     * @return \ProcessMaker\Models\RootElementRepository
     */
    public function getRootElementRepository()
    {
        return new \ProcessMaker\Models\RootElementRepository($this);
    }
}
