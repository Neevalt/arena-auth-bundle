<?php

namespace Neevalt\ArenaAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('arena_auth');
        $rootNode->children()
            ->scalarNode('app_nom')
            ->defaultValue('Nom Test')
            ->end()
            ->scalarNode('wsdlurl')
            ->defaultNull()
            ->end()
            ->arrayNode('roles')
            ->prototype('scalar')->end()
            ->defaultValue(['ROLE_GEST'])
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('user_loader_id')
            ->defaultValue('neevalt.arena-auth-bundle.arena_auth_user_loader')
            ->cannotBeEmpty()
            ->end()
            ->booleanNode('is_client_rsa')
            ->defaultFalse()
            ->end()
            ->scalarNode('redirect_logout')
            ->defaultValue('https://externet.ac-creteil.fr')
            ->cannotBeEmpty()
            ->end()
            ->booleanNode('is_strict_redirect')
            ->defaultFalse()
            ->end()
            ->scalarNode('user_class')
            ->defaultValue('Neevalt\ArenaAuthBundle\Security\User\ArenaAuthUser')
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('refresh_user')
            ->defaultValue('%kernel.debug%')
            ->cannotBeEmpty()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
