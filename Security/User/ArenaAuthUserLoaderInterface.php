<?php

namespace Neevalt\ArenaAuthBundle\Security\User;

/**
 * Implement this interface if you need to redefine the user's roles.
 *
 * Interface ArenaAuthUserLoaderInterface
 */
interface ArenaAuthUserLoaderInterface
{
    /**
     * Modifies the user before returning it.
     *
     * @param ArenaAuthUser $user
     *
     * @return ArenaAuthUser The modified user
     */
    public function loadUser(ArenaAuthUser $user): ArenaAuthUser;
}
