<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

abstract class AbstractCompilerPass implements CompilerPassInterface
{
    protected function getServiceClass(ContainerBuilder $container, string $serviceId): string
    {
        while (true) {
            $definition = $container->findDefinition($serviceId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $serviceId = $definition->getParent();

                continue;
            }

            return $definition->getClass();
        }
    }

    protected function getReflectionClass(ContainerBuilder $container, string $serviceId): \ReflectionClass
    {
        $className = $this->getServiceClass($container, $serviceId);
        $reflection = $container->getReflectionClass($className);
        if (null === $reflection) {
            throw new RuntimeException(sprintf('Invalid service "%s": class "%s" does not exist.', $serviceId, $className));
        }

        return $reflection;
    }
}
