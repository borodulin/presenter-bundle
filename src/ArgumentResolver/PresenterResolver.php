<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\ArgumentResolver;

use Borodulin\PresenterBundle\Presenter\Presenter;
use Borodulin\PresenterBundle\Presenter\PresenterInterface;
use Borodulin\PresenterBundle\Request\Expand\ExpandFactory;
use Borodulin\PresenterBundle\Request\Filter\FilterFactory;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestFactory;
use Borodulin\PresenterBundle\Request\Sort\SortFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PresenterResolver implements ArgumentValueResolverInterface
{
    private NormalizerInterface $normalizer;
    private SortFactory $sortRequestFactory;
    private ExpandFactory $expandRequestFactory;
    private FilterFactory $filterRequestFactory;
    private PaginationRequestFactory $paginationRequestFactory;

    public function __construct(
        NormalizerInterface $normalizer,
        SortFactory $sortRequestFactory,
        ExpandFactory $expandRequestFactory,
        FilterFactory $filterRequestFactory,
        PaginationRequestFactory $paginationRequestFactory
    ) {
        $this->normalizer = $normalizer;
        $this->sortRequestFactory = $sortRequestFactory;
        $this->expandRequestFactory = $expandRequestFactory;
        $this->filterRequestFactory = $filterRequestFactory;
        $this->paginationRequestFactory = $paginationRequestFactory;
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();

        if (!$type || !interface_exists($type)) {
            return false;
        }

        $reflection = new \ReflectionClass($type);

        return $reflection->isInterface()
            && $reflection->implementsInterface(PresenterInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $sort = $this->sortRequestFactory->tryCreateFromInputBug($request->query);
        $expand = $this->expandRequestFactory->tryCreateFromInputBug($request->query);
        $pagination = $this->paginationRequestFactory->createFromInputBug($request->query);
        $filter = $this->filterRequestFactory->tryCreateFromInputBug($request->query);

        yield new Presenter(
            $this->normalizer,
            $filter,
            $sort,
            $pagination,
            $expand
        );
    }
}
