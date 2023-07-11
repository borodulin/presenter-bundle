<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DependencyInjection;

use Borodulin\PresenterBundle\NameConverter\NameConverterRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PresenterGroupCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(NameConverterRegistry::class)) {
            return;
        }

        $nameConverters = [];
        foreach ($container->findTaggedServiceIds('presenter.group', true) as $serviceId => $tags) {
            $reflection = $this->getReflectionClass($container, $serviceId);

            foreach ($tags as $tag) {
                $group = $tag['group'] ?? 'default';
                if ($reflection->implementsInterface(NameConverterInterface::class)) {
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
