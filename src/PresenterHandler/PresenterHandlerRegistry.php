<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterHandler;

use Borodulin\PresenterBundle\PresenterContext\PresenterContextInterface;
use Doctrine\Persistence\Proxy;

class PresenterHandlerRegistry
{
    private array $handlers = [];

    public function __construct(
        array $handlers = []
    ) {
        foreach ($handlers as [$class, $group, $handler]) {
            $this->handlers[$class][$group] = $handler;
        }
    }

    public function hasPresenterHandlerForClass(string $class): bool
    {
        $class = $this->getHandledClass($class);

        return isset($this->handlers[$class]);
    }

    public function getPresenterHandlerForClass(string $class, string $group): array
    {
        $class = $this->getHandledClass($class);

        return $this->handlers[$class][$group]
            ?? $this->handlers[$class][PresenterContextInterface::DEFAULT_GROUP]
            ?? [null, null];
    }

    public function getCustomExpandFieldsForClass(string $class, string $group): ?CustomExpandInterface
    {
        [$presenterHandler] = $this->getPresenterHandlerForClass($class, $group);

        return $presenterHandler instanceof CustomExpandInterface ? $presenterHandler : null;
    }

    private function getHandledClass(string $class): string
    {
        if (is_subclass_of($class, Proxy::class)) {
            $class = get_parent_class($class);
        }

        return $class;
    }
}
