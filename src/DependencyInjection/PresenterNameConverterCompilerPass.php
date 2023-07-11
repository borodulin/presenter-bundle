<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DependencyInjection;

use Borodulin\PresenterBundle\NameConverter\NameConverterRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PresenterNameConverterCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(NameConverterRegistry::class)) {
            return;
        }

        $nameConverters = [];
        foreach ($container->findTaggedServiceIds('presenter.name_converter', true) as $serviceId => $tags) {
            $className = $this->getServiceClass($container, $serviceId);
            if (null === $className) {
                continue;
            }
            $reflection = $this->getReflectionClass($container, $serviceId, $className);

            if ($reflection->implementsInterface(NameConverterInterface::class)) {
                foreach ($tags as $tag) {
                    $group = $tag['group'] ?? 'default';
                    $nameConverters[$group] = new Reference($serviceId);
                }
            }
        }

        if ($nameConverters) {
            $commandDefinition = $container->getDefinition(NameConverterRegistry::class);
            $commandDefinition->setArgument('$nameConverters', $nameConverters);
        }
    }
}
