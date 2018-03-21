<?php

namespace ProcessMaker\Nayra\Contracts\Bpmn;

use ProcessMaker\Nayra\Bpmn\Collection;

/**
 * Interface to search paths through elements.
 *
 * @package ProcessMaker\Nayra\Contracts\Bpmn
 */
interface TraversableInterface
{

    /**
     * Find all the paths that complies with the $condition and $while.
     *
     * @param callable $condition
     * @param callable $while
     * @param array $path
     * @param array $passedthru
     * @param array $paths
     *
     * @return Collection
     */
    public function paths(callable  $condition, callable $while, $path = [], &$passedthru = [], &$paths = []);
}
