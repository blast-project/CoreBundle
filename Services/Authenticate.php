<?php

namespace Blast\CoreBundle\Services;

use FOS\UserBundle\Entity\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Authenticate
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * authencicateUser
     *
     * @param $username
     *
     * @return object
     *
     */
    public function authencicateUser($username)
    {
        if ($username)
        {
            $user = $this->userManager->findUserBy(['username' => $username]);

            if ($user)
            {
                $usernamePasswordToken = new UsernamePasswordToken($user->getUsername(), $user->getPlainPassword(), 'default', $user->getRoles());
                $this->tokenStorage->setToken($usernamePasswordToken);

                return $user;
            }
        }
        return false;
    }

    /**
     * setUserManager
     *
     * @param $userManager
     */
    public function setUserManager($userManager)
    {
        $this->userManager = $userManager;
    }
}
