<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DependencyInjection;

use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class PresenterExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(PresenterHandlerInterface::class)
            ->addTag('presenter.handler');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yml');
    }
}
