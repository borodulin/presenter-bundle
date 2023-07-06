<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Filter\FilterRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;

class DataProviderContext implements PresenterContextInterface
{
    public const FILTER_REQUEST = 'filter_request';
    public const SORT_REQUEST = 'sort_request';
    public const PAGINATION_REQUEST = 'pagination_request';

    private bool $paginationEnabled = true;
    private bool $filterEnabled = true;
    private bool $sortEnabled = true;

    public function __construct(
        public PaginationRequestInterface $paginationRequest,
        public ?FilterRequestInterface $filterRequest = null,
        public ?SortRequestInterface $sortRequest = null,
    ) {
    }

    public function toArray(): array
    {
        $context = [];
        if (null !== $this->filterRequest) {
            $context[self::FILTER_REQUEST] = $this->filterRequest;
        }
        if (null !== $this->sortRequest) {
            $context[self::SORT_REQUEST] = $this->sortRequest;
        }
        if (null !== $this->paginationRequest) {
            $context[self::PAGINATION_REQUEST] = $this->paginationRequest;
        }

        return $context;
    }

    public function isPaginationEnabled(): bool
    {
        return $this->paginationEnabled;
    }

    public function setPaginationEnabled(bool $paginationEnabled): void
    {
        $this->paginationEnabled = $paginationEnabled;
    }

    public function isFilterEnabled(): bool
    {
        return $this->filterEnabled;
    }

    public function setFilterEnabled(bool $filterEnabled): void
    {
        $this->filterEnabled = $filterEnabled;
    }

    public function isSortEnabled(): bool
    {
        return $this->sortEnabled;
    }

    public function setSortEnabled(bool $sortEnabled): void
    {
        $this->sortEnabled = $sortEnabled;
    }
}
