<?php

namespace AppBundle\Entity;

/**
 * Addressable
 */
abstract class Addressable extends Traceable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $postalcode;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @var boolean
     */
    private $npai = false;

    /**
     * @var string
     */
    private $email;

    /**
     * @var boolean
     */
    private $emailNpai = false;

    /**
     * @var boolean
     */
    private $emailNoNewsletter = false;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $vcardUid;

    /**
     * @var boolean
     */
    private $confirmed = true;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return Addressable
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Addressable
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postalcode
     *
     * @param string $postalcode
     *
     * @return Addressable
     */
    public function setPostalcode($postalcode)
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    /**
     * Get postalcode
     *
     * @return string
     */
    public function getPostalcode()
    {
        return $this->postalcode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Addressable
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Addressable
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set npai
     *
     * @param boolean $npai
     *
     * @return Addressable
     */
    public function setNpai($npai)
    {
        $this->npai = $npai;

        return $this;
    }

    /**
     * Get npai
     *
     * @return boolean
     */
    public function getNpai()
    {
        return $this->npai;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Addressable
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailNpai
     *
     * @param boolean $emailNpai
     *
     * @return Addressable
     */
    public function setEmailNpai($emailNpai)
    {
        $this->emailNpai = $emailNpai;

        return $this;
    }

    /**
     * Get emailNpai
     *
     * @return boolean
     */
    public function getEmailNpai()
    {
        return $this->emailNpai;
    }

    /**
     * Set emailNoNewsletter
     *
     * @param boolean $emailNoNewsletter
     *
     * @return Addressable
     */
    public function setEmailNoNewsletter($emailNoNewsletter)
    {
        $this->emailNoNewsletter = $emailNoNewsletter;

        return $this;
    }

    /**
     * Get emailNoNewsletter
     *
     * @return boolean
     */
    public function getEmailNoNewsletter()
    {
        return $this->emailNoNewsletter;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Addressable
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set vcardUid
     *
     * @param string $vcardUid
     *
     * @return Addressable
     */
    public function setVcardUid($vcardUid)
    {
        $this->vcardUid = $vcardUid;

        return $this;
    }

    /**
     * Get vcardUid
     *
     * @return string
     */
    public function getVcardUid()
    {
        return $this->vcardUid;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return Addressable
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }
}
