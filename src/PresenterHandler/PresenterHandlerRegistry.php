<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterHandler;

use Doctrine\Persistence\Proxy;

class PresenterHandlerRegistry
{
    private array $handlers = [];

    /**
     * @param PresenterHandlerInterface[] $handlers
     */
    public function __construct(
        array $handlers = []
    ) {
        foreach ($handlers as $class => $handler) {
            $this->handlers[$class] = $handler;
        }
    }

    public function getPresenterHandlerForClass(string $class): ?PresenterHandlerInterface
    {
        if (is_subclass_of($class, Proxy::class)) {
            $class = get_parent_class($class);
        }
        return $this->handlers[$class] ?? null;
    }

    public function getCustomExpandFieldsForClass(string $class): ?CustomExpandInterface
    {
        $converter = $this->getPresenterHandlerForClass($class);

        return $converter instanceof CustomExpandInterface ? $converter : null;
    }
}
