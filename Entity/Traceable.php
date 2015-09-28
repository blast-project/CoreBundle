<?php

namespace Librinfo\CoreBundle\Entity;

/**
 * Traceable
 */
abstract class Traceable
{
    /**
     * @var guid
     */
    private $id;

    /**
     * @var guid
     */
    private $user_id;

    /**
     * @var boolean
     */
    private $automatic = false;


    /**
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param guid $userId
     *
     * @return Traceable
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return guid
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set automatic
     *
     * @param boolean $automatic
     *
     * @return Traceable
     */
    public function setAutomatic($automatic)
    {
        $this->automatic = $automatic;

        return $this;
    }

    /**
     * Get automatic
     *
     * @return boolean
     */
    public function getAutomatic()
    {
        return $this->automatic;
    }
}

