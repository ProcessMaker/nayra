<?php

namespace ProcessMaker\Nayra\Contracts\Repositories;

/**
 * Repository for ItemDefinitionInterface
 *
 * @package ProcessMaker\Nayra\Contracts\Repositories
 */
interface RootElementRepositoryInterface
{

    /**
     * Create a new instance.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\ItemDefinitionInterface
     */
    public function createItemDefinitionInstance();

    /**
     * Create a Message.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageInterface
     */
    public function createMessageInstance();

    /**
     * Create a MessageEventDefinition.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\MessageEventDefinitionInterface
     */
    public function createMessageEventDefinitionInstance();
}
