<?php

namespace Neevalt\ArenaAuthBundle\DependencyInjection\Security\Factory;

use Neevalt\ArenaAuthBundle\Security\Authentication\Provider\ArenaAuthProvider;
use Neevalt\ArenaAuthBundle\Security\Firewall\ArenaAuthListener;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ArenaAuthFactory implements SecurityFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        $providerId = 'security.authentication.provider.'.$id;
        $container
            ->setDefinition($providerId, new ChildDefinition(ArenaAuthProvider::class))
            ->replaceArgument('$userProvider', new Reference($userProvider))
        ;

        $listenerId = "security.authentication.listener.${id}";
        $container->setDefinition($listenerId, new ChildDefinition(ArenaAuthListener::class));

        return [$providerId, $listenerId, $defaultEntryPoint];
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'rsa_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
    }
}
