<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\ArgumentResolver;

use Borodulin\PresenterBundle\PresenterContext\DataProviderContext;
use Borodulin\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Borodulin\PresenterBundle\PresenterContext\PresenterContextInterface;
use Borodulin\PresenterBundle\Request\Expand\ExpandFactory;
use Borodulin\PresenterBundle\Request\Filter\FilterFactory;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestFactory;
use Borodulin\PresenterBundle\Request\Sort\SortFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PresenterContextResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly DataProviderContextFactory $presenterContextFactory,
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
            && $reflection->implementsInterface(PresenterContextInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->presenterContextFactory->createFromInputBug($request->query);
    }
}
