<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Expand\ExpandFactory;
use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
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
        private readonly ExpandFactory $expandRequestFactory,
        private readonly FilterFactory $filterRequestFactory,
        private readonly PaginationRequestFactory $paginationRequestFactory
    ) {
    }

    public function createFromInputBug(InputBag $inputBag): DataProviderContext
    {
        $sort = $this->sortRequestFactory->tryCreateFromInputBug($inputBag);
        $expand = $this->expandRequestFactory->tryCreateFromInputBug($inputBag);
        $pagination = $this->paginationRequestFactory->createFromInputBug($inputBag);
        $filter = $this->filterRequestFactory->tryCreateFromInputBug($inputBag);

        return new DataProviderContext(
            filterRequest: $filter,
            sortRequest: $sort,
            paginationRequest: $pagination,
            expandRequest: $expand,
        );
    }

    public function createFromArrayContext(array $context): DataProviderContext
    {
        $expandRequest = $context[PresenterContextInterface::EXPAND_REQUEST] ?? null;
        unset($context[PresenterContextInterface::EXPAND_REQUEST]);
        if (!$expandRequest instanceof ExpandRequestInterface && isset($context[PresenterContextInterface::EXPAND])) {
            $expandRequest = $this->expandRequestFactory->createFromArray($context[PresenterContextInterface::EXPAND]);
        }
        $filterRequest = $context[PresenterContextInterface::FILTER_REQUEST] ?? null;
        $sortRequest = $context[PresenterContextInterface::SORT_REQUEST] ?? null;
        $paginationRequest = $context[PresenterContextInterface::PAGINATION_REQUEST] ?? null;

        return new DataProviderContext(
            filterRequest: $filterRequest instanceof FilterRequestInterface ? $filterRequest : null,
            sortRequest: $sortRequest instanceof SortRequestInterface ? $sortRequest : null,
            paginationRequest: $paginationRequest instanceof PaginationRequestInterface
                ? $paginationRequest : $this->paginationRequestFactory->createDefault(),
            expandRequest: $expandRequest instanceof ExpandRequestInterface ? $expandRequest : null,
            group: $context[PresenterContextInterface::GROUP] ?? PresenterContextInterface::DEFAULT_GROUP,
        );
    }
}
