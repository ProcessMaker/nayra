<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Repository for TokenInterface
 *
 */
interface TokenRepositoryInterface extends RepositoryInterface
{

    /**
     * Create a token instance.
     *
     * @return TokenInterface
     */
    public function createTokenInstance();

    /**
     * Load a token from a persistent storage.
     *
     * @param string $uid
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface
     */
    public function loadTokenByUid($uid);

    /**
     * Create or update a activity to a persistent storage.
     *
     * @param \ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface $token
     * @param bool $saveChildElements
     *
     * @return $this
     */
    public function store(TokenInterface $token, $saveChildElements = false);
}
