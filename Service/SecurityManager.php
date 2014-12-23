<?php

namespace Cravler\FayeAppBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class SecurityManager
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var UserProviderInterface
     */
    private $provider;

    /**
     * @param string $secret
     * @param UserProviderInterface $provider
     */
    public function __construct($secret, UserProviderInterface $provider = null)
    {
        $this->secret = $secret;
        $this->provider = $provider;
    }

    /**
     * @param string $username
     * @return string
     */
    public function createToken($username)
    {
        return hash_hmac('sha256', $username, $this->secret);
    }

    /**
     * @return string
     */
    public function createSystemToken()
    {
        return hash_hmac('sha512', '--[system-token]--', $this->secret);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isSystem(array $data)
    {
        if (isset($data['system']) && $data['system'] == $this->createSystemToken()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getUser(array $data)
    {
        if (isset($data['username']) && isset($data['token'])) {
            if ($this->createToken($data['username']) == $data['token']) {
                return $this->findUser($data['username']);
            }
        }

        return null;
    }

    /**
     * @param string $username
     * @return null|UserInterface
     */
    public function findUser($username)
    {
        $user = null;
        if ($this->provider && $username) {
            try {
                $user = $this->provider->loadUserByUsername($username);
            } catch (UsernameNotFoundException $e) {
                //
            }
        }

        return $user;
    }
}
