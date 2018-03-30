<?php

namespace Neevalt\ArenaAuthBundle\Security\Firewall;

use Neevalt\ArenaAuthBundle\Security\Authentication\Token\ArenaAuthToken;
use Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUser;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class ArenaAuthListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;
    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;
    /**
     * @var bool
     */
    private $refreshUser;

    /**
     * ArenaAuthListener constructor.
     *
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param bool                           $refreshUser
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        bool $refreshUser
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->refreshUser = $refreshUser;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(GetResponseEvent $event)
    {
        if (null !== ($token = $this->tokenStorage->getToken()) &&
                $token instanceof ArenaAuthToken && $token->isAuthenticated() && !$this->refreshUser
        ) {
            return;
        }

        $token = new ArenaAuthToken();
        $token->setUser(ArenaAuthUser::DEFAULT_USERNAME);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);
        } catch (AuthenticationException $failed) {
            $this->tokenStorage->setToken(null);
        }
    }
}
