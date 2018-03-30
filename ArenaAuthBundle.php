<?php

namespace Neevalt\ArenaAuthBundle;

use Neevalt\ArenaAuthBundle\DependencyInjection\ArenaAuthCompilerPass;
use Neevalt\ArenaAuthBundle\DependencyInjection\Security\Factory\ArenaAuthFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ArenaAuthBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ArenaAuthCompilerPass());
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ArenaAuthFactory());
    }
}
