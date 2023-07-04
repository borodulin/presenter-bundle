<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

use Borodulin\PresenterBundle\Request\Expand\ExpandRequestInterface;
use Borodulin\PresenterBundle\Request\Filter\FilterRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Sort\SortRequestInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DataProviderContext implements PresenterContextInterface
{
    public function __construct(
        public ?FilterRequestInterface $filterRequest = null,
        public ?SortRequestInterface $sortRequest = null,
        public ?PaginationRequestInterface $paginationRequest = null,
        public ?ExpandRequestInterface $expandRequest = null,
        public ?NameConverterInterface $nameConverter = null,
        public string $group = self::DEFAULT_GROUP,
    ) {
    }

    public function toArray(): array
    {
        $context = [];
        if (null !== $this->expandRequest) {
            $context[self::EXPAND_REQUEST] = $this->expandRequest;
        }
        if (null !== $this->filterRequest) {
            $context[self::FILTER_REQUEST] = $this->filterRequest;
        }
        if (null !== $this->sortRequest) {
            $context[self::SORT_REQUEST] = $this->sortRequest;
        }
        if (null !== $this->paginationRequest) {
            $context[self::PAGINATION_REQUEST] = $this->paginationRequest;
        }
        if (null !== $this->nameConverter) {
            $context[self::NAME_CONVERTER] = $this->nameConverter;
        }
        $context[self::GROUP] = $this->group;

        return $context;
    }
}
