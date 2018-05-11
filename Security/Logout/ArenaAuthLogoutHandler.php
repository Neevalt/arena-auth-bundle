<?php

namespace Neevalt\ArenaAuthBundle\Security\Logout;

use Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class ArenaAuthLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var bool
     */
    private $isStrictRedirect;
    /**
     * @var string
     */
    private $redirectLogout;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * LogoutHandler constructor.
     *
     * @param bool                  $isStrictRedirect
     * @param string                $redirectLogout
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(bool $isStrictRedirect, string $redirectLogout, TokenStorageInterface $tokenStorage)
    {
        $this->isStrictRedirect = $isStrictRedirect;
        $this->redirectLogout = $redirectLogout;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($this->isStrictRedirect || !$user instanceof ArenaAuthUser || empty($user->getOrigine())) {
            $redirect = $this->redirectLogout;
        } else {
            $redirect = $user->getOrigine();
        }
        if (preg_match('#((.*).fr).*#i', $redirect, $var)) {
            $redirect = $var[1];
        }
        return new RedirectResponse($redirect);
    }
}
