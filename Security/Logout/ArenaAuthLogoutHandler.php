<?php

namespace Neevalt\ArenaAuthBundle\Security\Logout;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class ArenaAuthLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var string|null
     */
    private $redirectLogout;

    /**
     * LogoutHandler constructor.
     *
     * @param string|null $redirectLogout
     */
    public function __construct(?string $redirectLogout)
    {
        $this->redirectLogout = $redirectLogout;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        if (null !== $this->redirectLogout) {
            $redirect = $this->redirectLogout;
        } else {
            $redirect = '/';
        }
        return new RedirectResponse($redirect);
    }
}
