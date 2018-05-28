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
        } elseif (preg_match('#(.*\.fr).*#i', $request->getUri(), $var)) {
            $redirect = $var[0];
        } else {
            $redirect = 'https://externet.ac-creteil.fr';
        }
        return new RedirectResponse($redirect);
    }
}
