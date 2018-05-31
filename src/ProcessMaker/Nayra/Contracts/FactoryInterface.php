<?php

namespace ProcessMaker\Nayra\Contracts;

interface FactoryInterface
{

    /**
     * Creates an instance of the interface passed
     *
     * @param string $interfaceName Fully qualified name of the interface
     * @param array ...$constructorArguments arguments of class' constructor
     *
     * @return mixed
     */
    public function createInstanceOf($interfaceName, ...$constructorArguments);
}
