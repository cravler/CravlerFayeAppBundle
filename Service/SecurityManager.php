<?php

namespace Cravler\FayeAppBundle\Service;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class SecurityManager
{
    private string $secret;

    private ?UserProviderInterface $provider;

    public function __construct(
        string $secret,
        ?UserProviderInterface $provider = null
    ) {
        $this->secret = $secret;
        $this->provider = $provider;
    }

    public function createToken(string $userIdentifier): string
    {
        return \hash_hmac('sha256', $userIdentifier, $this->secret);
    }

    public function createSystemToken(): string
    {
        return \hash_hmac('sha512', '--[system-token]--', $this->secret);
    }

    /**
     * @param array{'system'?: string} $data
     */
    public function isSystem(array $data): bool
    {
        if (isset($data['system']) && $data['system'] == $this->createSystemToken()) {
            return true;
        }

        return false;
    }

    /**
     * @param array{'userIdentifier'?: string, 'token'?: string} $data
     */
    public function getUser(array $data): ?UserInterface
    {
        if (isset($data['userIdentifier']) && isset($data['token'])) {
            if ($this->createToken($data['userIdentifier']) == $data['token']) {
                return $this->findUser($data['userIdentifier']);
            }
        }

        return null;
    }

    private function findUser(string $userIdentifier): ?UserInterface
    {
        $user = null;

        if ($this->provider && $userIdentifier) {
            try {
                if (\method_exists($this->provider, 'loadUserByIdentifier')) {
                    $user = $this->provider->loadUserByIdentifier($userIdentifier);
                } else {
                    $user = $this->provider->loadUserByUsername($userIdentifier);
                }
            } catch (UserNotFoundException $e) {
                //
            }
        }

        return $user;
    }
}
