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

        if (!($type && class_exists($type))) {
            return false;
        }

        $reflection = new \ReflectionClass($type);

        return $reflection->implementsInterface(DataProviderInterface::class)
            && $this->container->has($type);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dataProvider = $this->container->get($argument->getType());
        if ($dataProvider instanceof PresenterContextAwareInterface) {
            $context = $this->dataProviderContextFactory->createFromInputBug($request->query);
            $dataProvider->setContext($context);
        }
        yield $dataProvider;
    }
}
