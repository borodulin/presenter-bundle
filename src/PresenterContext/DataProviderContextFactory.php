<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Filter\FilterFactory;
use Borodulin\PresenterBundle\Request\Filter\FilterRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestFactory;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Sort\SortFactory;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;
use Symfony\Component\HttpFoundation\InputBag;

class DataProviderContextFactory
{
    public function __construct(
        private readonly SortFactory $sortRequestFactory,
        private readonly FilterFactory $filterRequestFactory,
        private readonly PaginationRequestFactory $paginationRequestFactory
    ) {
    }

    public function createFromInputBug(InputBag $inputBag): DataProviderContext
    {
        $sort = $this->sortRequestFactory->tryCreateFromInputBug($inputBag);
        $pagination = $this->paginationRequestFactory->createFromInputBug($inputBag);
        $filter = $this->filterRequestFactory->tryCreateFromInputBug($inputBag);

        return new DataProviderContext(
            paginationRequest: $pagination,
            filterRequest: $filter,
            sortRequest: $sort,
        );
    }

    public function createFromArrayContext(array $context): DataProviderContext
    {
        $filterRequest = $context[DataProviderContext::FILTER_REQUEST] ?? null;
        $sortRequest = $context[DataProviderContext::SORT_REQUEST] ?? null;
        $paginationRequest = $context[DataProviderContext::PAGINATION_REQUEST] ?? null;

        return new DataProviderContext(
            paginationRequest: $paginationRequest instanceof PaginationRequestInterface
                ? $paginationRequest : $this->paginationRequestFactory->createDefault(),
            filterRequest: $filterRequest instanceof FilterRequestInterface ? $filterRequest : null,
            sortRequest: $sortRequest instanceof SortRequestInterface ? $sortRequest : null,
        );
    }
}
