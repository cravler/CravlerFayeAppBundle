<?php

namespace Cravler\FayeAppBundle\Service;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class SecurityManager
{
    /**
     * @param ?UserProviderInterface<UserInterface> $provider
     */
    public function __construct(
        private readonly string $secret,
        private readonly ?UserProviderInterface $provider = null,
    ) {
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
        if (isset($data['system']) && $data['system'] === $this->createSystemToken()) {
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
            if ($this->createToken($data['userIdentifier']) === $data['token']) {
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
                $user = $this->provider->loadUserByIdentifier($userIdentifier);
            } catch (UserNotFoundException $e) {
            }
        }

        return $user;
    }
}
