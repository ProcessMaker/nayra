<?php
/**
 * Created by PhpStorm.
 * User: dante
 * Date: 6/4/18
 * Time: 8:56 AM
 */

namespace ProcessMaker\Nayra\Bpmn\Model;

use ProcessMaker\Nayra\Bpmn\DataStoreTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\DataStoreInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface;
use ProcessMaker\Nayra\Contracts\FactoryInterface;

/**
 * Application
 *
 * @package ProcessMaker\Models
 */
class DataStore implements DataStoreInterface
{
    use DataStoreTrait;

    private $data = [];

    /**
     *
     * @var \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface
     */
    private $process;

    /**
     * Get owner process.
     *
     * @return ProcessInterface
     */
    public function getOwnerProcess()
    {
        return $this->process;
    }

    /**
     * Get Process of the application.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ProcessInterface $process
     *
     * @return ProcessInterface
     */
    public function setOwnerProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * @return \ProcessMaker\Nayra\Contracts\Repositories\StorageInterface
     */
    public function getFactory()
    {
        // TODO: Implement getFactory() method.
    }

    /**
     * @param FactoryInterface $factory
     * @return $this
     */
    public function setFactory(FactoryInterface $factory)
    {
        // TODO: Implement setFactory() method.
    }

    /**
     * Get data from store.
     *
     * @param mixed $name
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
     * @param array $data
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
     * @param string $name
     * @param mixed $data
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
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        // TODO: Implement setStare() method.
    }

    /**
     * Get item subject
     *
     */
    public function getItemSubject()
    {

    }
}
