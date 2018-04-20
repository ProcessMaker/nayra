<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\DataStoreTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;

/**
 * Application
 *
 * @package ProcessMaker\Models
 */
class DataStore implements DataStoreInterface
{
    use DataStoreTrait,
        LocalPropertiesTrait;

    private $data = [];

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private $process;

    public function __construct()
    {

    }

    public function getOwnerProcess()
    {
        return $this->process;
    }

    /**
     * Get Process of the application.
     *
     * @return ProcessInterface
     */
    public function setOwnerProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * @return \ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface
     */
    public function getFactory()
    {
        // TODO: Implement getFactory() method.
    }

    /**
     * @param \ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface $factory
     * @return $this
     */
    public function setFactory(\ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface $factory)
    {
        // TODO: Implement setFactory() method.
    }

    /**
     * Get data from store.
     *
     * @param $name
     *
     * @return mixed
     */
    public function getData($name = null)
    {
        return $name === null ? $this->data : $this->data[$name];
    }

    /**
     * Set data of the store.
     *
     * @param $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Put data to store.
     *
     * @param $name
     * @param $data
     *
     * @return $this
     */
    public function putData($name, $data)
    {
        $this->data[$name] = $data;
        return $this;
    }

    /**
     * Get item state.
     *
     * @return mixed
     */
    public function getState()
    {
        // TODO: Implement getState() method.
    }

    /**
     * Set item state.
     *
     * @param $state
     *
     * @return $this
     */
    public function setState($state)
    {
        // TODO: Implement setStare() method.
    }

    public function getItemSubject()
    {
        
    }
}
