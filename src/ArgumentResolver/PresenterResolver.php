<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\ArgumentResolver;

use Borodulin\PresenterBundle\Attribute\DataProvider;
use Borodulin\PresenterBundle\Attribute\Presenter as PresenterAttribute;
use Borodulin\PresenterBundle\Presenter\Presenter;
use Borodulin\PresenterBundle\Presenter\PresenterInterface;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Borodulin\PresenterBundle\PresenterContext\ObjectContext;
use Borodulin\PresenterBundle\PresenterContext\ObjectContextFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class PresenterResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly DataProviderContextFactory $dataProviderContextFactory,
        private readonly ObjectContextFactory $objectContextFactory,
        private readonly ContainerInterface $container,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (!($type && interface_exists($type))) {
            return false;
        }

        $reflection = new \ReflectionClass($type);

        return $reflection->isInterface()
            && $reflection->implementsInterface(PresenterInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $objectContext = $this->objectContextFactory->createFromInputBug($request->query);
        $dataProviderContext = $this->dataProviderContextFactory->createFromInputBug($request->query);

        /** @var PresenterAttribute $attribute */
        foreach ($argument->getAttributes(PresenterAttribute::class) as $attribute) {
            $objectContext->group = $attribute->group ?? ObjectContext::DEFAULT_GROUP;
            if (null !== $attribute->nameConverter) {
                $nameConverter = $this->container->get($attribute->nameConverter);
                if (!$nameConverter instanceof NameConverterInterface) {
                    throw new \InvalidArgumentException();
                }
                $objectContext->nameConverter = $nameConverter;
            }
        }

        /** @var DataProvider $attribute */
        foreach ($argument->getAttributes(DataProvider::class) as $attribute) {
            $dataProviderContext->setPaginationEnabled($attribute->paginated);
            $dataProviderContext->setFilterEnabled($attribute->filtered);
            $dataProviderContext->setSortEnabled($attribute->sorted);
        }

        yield new Presenter($objectContext, $dataProviderContext);
    }
}
