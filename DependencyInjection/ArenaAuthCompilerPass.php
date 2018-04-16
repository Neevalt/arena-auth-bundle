<?php

namespace Neevalt\ArenaAuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ArenaAuthCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $className = $container->getParameter('neevalt.arena-auth-bundle.user_loader_id');
        $def = $container->findDefinition($className);
        $definition = $container->findDefinition('neevalt.arena-auth-bundle.user_provider');
        $definition->setArgument('$userLoader', $def);
    }
}