<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\ArgumentResolver;

use Borodulin\PresenterBundle\DataProvider\DataProviderInterface;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Borodulin\PresenterBundle\PresenterContext\PresenterContextAwareInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DataProviderResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly DataProviderContextFactory $dataProviderContextFactory,
        private readonly ContainerInterface $container,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (!($type && interface_exists($type))) {
            return false;
        }

        $controller = $this->getController($request);

        $reflection = new \ReflectionClass($type);

        return $reflection->isInterface()
            && $reflection->implementsInterface(DataProviderInterface::class)
            && $this->container->has($controller) && $this->container->get($controller)->has($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $controller = $this->getController($request);

        $dataProvider = $this->container->get($controller)->get($argument->getName());
        if ($dataProvider instanceof PresenterContextAwareInterface) {
            $context = $this->dataProviderContextFactory->createFromInputBug($request->query);
            $dataProvider->setContext($context);
        }
        yield $dataProvider;
    }

    private function getController(Request $request): string
    {
        $controller = $request->attributes->get('_controller');
        if (\is_array($controller)) {
            $controller = $controller[0] . '::' . $controller[1];
        }

        if ('\\' === $controller[0]) {
            $controller = ltrim($controller, '\\');
        }

        if (!$this->container->has($controller)) {
            $i = strrpos($controller, ':');
            $controller = substr($controller, 0, $i) . strtolower(substr($controller, $i));
        }

        return $controller;
    }
}
