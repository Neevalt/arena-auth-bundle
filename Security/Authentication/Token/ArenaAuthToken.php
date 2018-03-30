<?php

namespace Neevalt\ArenaAuthBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ArenaAuthToken extends AbstractToken
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return '';
    }
}
