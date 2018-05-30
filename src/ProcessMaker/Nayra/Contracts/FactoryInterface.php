<?php
/**
 * Created by PhpStorm.
 * User: dante
 * Date: 5/30/18
 * Time: 8:23 AM
 */

namespace ProcessMaker\Nayra\Contracts;


use InvalidArgumentException;

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
    public function getInstanceOf($interfaceName, ...$constructorArguments);
}