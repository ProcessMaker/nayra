<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Bpmn\TokenTrait;
use ProcessMaker\Nayra\Contracts\Bpmn\TokenInterface;

/**
 * Token implementation.
 *
 * @package ProcessMaker\Models
 */
class Token implements TokenInterface
{
    use TokenTrait,
        LocalProcessTrait,
        LocalPropertiesTrait;

    const PROPERTY_STATUS = 'STATUS';

    /**
     * Get token internal status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getProperty(static::PROPERTY_STATUS);
    }

    /**
     * Set token internal status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setProperty(static::PROPERTY_STATUS, $status);
        return $this;
    }
}
