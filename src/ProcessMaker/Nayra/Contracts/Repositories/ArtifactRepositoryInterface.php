<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface;

/**
 * Repository for ArtifactInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface ArtifactRepositoryInterface extends RepositoryInterface
{

    /**
     * Create an artifact instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface
     */
    public function createArtifactInstance();

    /**
     * Load a artifact from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface
     */
    public function loadArtifactByUid($uid);

    /**
     * Create or update a artifact to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\ArtifactInterface $artifact
     * @param $saveChildElements
     *
     * @return $this
     */
    public function store(ArtifactInterface $artifact, $saveChildElements=false);
}
