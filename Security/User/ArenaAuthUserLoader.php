<?php

namespace Neevalt\ArenaAuthBundle\Security\User;

class ArenaAuthUserLoader implements ArenaAuthUserLoaderInterface
{
    /**
     * @var array
     */
    private $roles;

    /**
     * ArenaUserLoader constructor.
     *
     * @param array $roles
     */
    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUser(ArenaAuthUser $user): ArenaAuthUser
    {
        $user->setRoles($this->roles);

        return $user;
    }
}
