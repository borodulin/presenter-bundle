<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DependencyInjection;

use Borodulin\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PresenterHandlerCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(PresenterHandlerRegistry::class)) {
            return;
        }

        $handlers = [];
        foreach ($container->findTaggedServiceIds('presenter.handler', true) as $serviceId => $tags) {
            $className = $this->getServiceClass($container, $serviceId);
            if (null === $className) {
                throw new \RuntimeException(sprintf('Invalid service "%s": class is not found.', $serviceId));
            }
            $reflection = $this->getReflectionClass($container, $serviceId, $className);

            foreach ($tags as $tag) {
                $methodName = $tag['method'] ?? '__invoke';
                $handles = $tag['handles'] ?? null;
                $group = $tag['group'] ?? 'default';

                if (null === $handles) {
                    try {
                        $method = $reflection->getMethod($methodName);
                    } catch (\ReflectionException) {
                        throw new \RuntimeException(sprintf('Invalid converter handler: class "%s" must have an "%s()" method.', $reflection->getName(), $methodName));
                    }
                    if (0 === $method->getNumberOfRequiredParameters()) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: method "%s::__invoke()" requires at least one argument, first one being the object it handles.',
                            $reflection->getName()
                        ));
                    }
                    $parameters = $method->getParameters();
                    $type = $parameters[0]->getType();
                    if (!$type) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: argument "$%s" of method "%s::%s()" must have a type-hint corresponding to the object class it handles.',
                            $parameters[0]->getName(),
                            $reflection->getName(),
                            $methodName
                        ));
                    }

                    if ($type->isBuiltin()) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: type-hint of argument "$%s" in method "%s::%s()" must be a class , "%s" given.',
                            $parameters[0]->getName(),
                            $reflection->getName(),
                            $methodName,
                            $type
                        ));
                    }
                    $handles = (string) $type;
                }

                $handlers[$handles . ':' . $group] = [$handles, $group, new Reference($reflection->getName()), $methodName];
            }
        }

        if ($handlers) {
            $commandDefinition = $container->getDefinition(PresenterHandlerRegistry::class);
            $commandDefinition->setArgument('$handlers', $handlers);
        }
    }
}
