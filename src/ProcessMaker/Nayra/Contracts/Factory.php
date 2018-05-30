<?php

namespace ProcessMaker\Nayra\Contracts;


use InvalidArgumentException;

/**
 * Class that create instances of classes based on the mappings interface-concrete class passed to it.
 *
 * @package ProcessMaker\Nayra\Contracts
 */
class Factory implements FactoryInterface
{
    private $config;

    public function __construct(array $configuration)
    {
        $this->config = $configuration;
    }

    /**
     * Creates an instance of the interface passed
     *
     * @param string $interfaceName Fully qualified name of the interface
     * @param array ...$constructorArguments arguments of class' constructor
     *
     * @return mixed
     */
    public function getInstanceOf($interfaceName, ...$constructorArguments)
    {
        if (!array_key_exists($interfaceName, $this->config)) {
            throw new InvalidArgumentException("Can't determine the class to instantiate for the interface '$interfaceName'");
        }

        $classToInstantiate = $this->config[$interfaceName];
        $result = new $classToInstantiate (...$constructorArguments);
        return $result;
    }
}
