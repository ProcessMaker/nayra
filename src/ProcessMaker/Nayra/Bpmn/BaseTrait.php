<?php

namespace ProcessMaker\Nayra\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;
use ProcessMaker\Nayra\Contracts\RepositoryInterface;
use ReflectionClass;

/**
 * BaseTrait
 *
 * @package ProcessMaker\Nayra\Bpmn
 */
trait BaseTrait
{
    private $properties = [];

    /**
     * Factory used to build this element.
     *
     * @var RepositoryInterface $factory
     */
    private $repository;

    /**
     * BPMN document of this object.
     *
     * @var StorageInterface $ownerDocument
     */
    private $ownerDocument;

    /**
     * BaseTrait constructor.
     *
     * @param array ...$args
     */
    public function __construct(...$args)
    {
        $this->bootElement($args);
    }

    /**
     * Call the initFunctions defined in traits.
     *
     * @param array $args
     */
    protected function bootElement(array $args)
    {
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getMethods() as $method) {
            $name = $method->getName();
            if (substr($name, 0, 4) === 'init') {
                call_user_func_array([$this, $name], $args);
            }
        }
    }

    /**
     * Get the factory used to build this element.
     *
     * @return \ProcessMaker\Nayra\Contracts\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set the factory used to build this element.
     *
     * @param \ProcessMaker\Nayra\Contracts\RepositoryInterface $repository
     *
     * @return $this
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Get the owner BPMN document of this object.
     *
     * @return \ProcessMaker\Nayra\Contracts\StorageInterface
     */
    public function getOwnerDocument()
    {
        return $this->ownerDocument;
    }

    /**
     * Set the owner BPMN document of this object.
     *
     * @param \ProcessMaker\Nayra\Contracts\StorageInterface $ownerDocument
     *
     * @return $this
     */
    public function setOwnerDocument(StorageInterface $ownerDocument)
    {
        $this->ownerDocument = $ownerDocument;
        return $this;
    }

    /**
     * Get properties.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set properties.
     *
     * @param array $properties
     * @return $this
     */
    public function setProperties(array $properties)
    {
        foreach($properties as $name => $value) {
            $setter = 'set' . $name;
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            } else {
                $this->setProperty($name, $value);
            }
        }
        return $this;
    }

    /**
     * Set a property.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
        return $this;
    }

    /**
     * Get a property.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($name, $default = null)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : $default;
    }

    /**
     * Add value to collection property.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function addProperty($name, $value)
    {
        $this->properties[$name] = isset($this->properties[$name]) ? $this->properties[$name] : new Collection;
        $this->properties[$name]->push($value);
        return $this;
    }

    /**
     * Get Id of the element.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getProperty(static::BPMN_PROPERTY_ID);
    }

    /**
     * Set Id of the element.
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setProperty(static::BPMN_PROPERTY_ID, $id);
        return $this;
    }

    /**
     * Get the name of the element.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->getProperty(static::BPMN_PROPERTY_NAME);
    }

    /**
     * Set the name of the element
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        return $this->setProperty(static::BPMN_PROPERTY_NAME, $name);
    }
}
