<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle;

use Borodulin\PresenterBundle\DependencyInjection\PresenterExtension;
use Borodulin\PresenterBundle\DependencyInjection\PresenterNameConverterCompilerPass;
use Borodulin\PresenterBundle\DependencyInjection\PresenterHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PresenterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PresenterHandlerCompilerPass());
        $container->addCompilerPass(new PresenterNameConverterCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new PresenterExtension();
    }
}
