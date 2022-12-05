<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Borodulin\PresenterBundle\DependencyInjection\PresenterExtension;
use Borodulin\PresenterBundle\DependencyInjection\PresenterHandlerFactoryPass;

class PresenterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PresenterHandlerFactoryPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new PresenterExtension();
    }
}
