<?php

namespace Librinfo\CoreBundle\Entity;

use Librinfo\BaseEntitiesBundle\Entity\Traits\BaseEntity;
use Librinfo\BaseEntitiesBundle\Entity\Traits\Jsonable;

/**
 * SelectChoice
 */
class SelectChoice implements \JsonSerializable
{
    use BaseEntity;
    use Jsonable;
    
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $value;

    /**
     * Set label
     *
     * @param string $label
     *
     * @return SelectChoice
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return SelectChoice
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
