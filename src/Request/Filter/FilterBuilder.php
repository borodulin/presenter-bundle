<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Filter;

use Borodulin\PresenterBundle\DataProvider\CustomFilterInterface;
use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderFilterInterface;

class FilterBuilder
{
    public function filter(
        FilterRequestInterface $filterRequest,
        QueryBuilderFilterInterface $filterQueryBuilder,
        ?CustomFilterInterface $customFilter
    ): void {
        if (null !== $customFilter) {
            $filterMap = [];
            foreach ($customFilter->getFilterFields() as $filterName => $fieldName) {
                if (\is_int($filterName) && \is_string($fieldName)) {
                    $filterMap[$fieldName] = ["$fieldName", null];
                } elseif (\is_string($filterName)) {
                    $filterMap[$filterName] = [$fieldName, null];
                }
            }
        } else {
            $filterMap = $filterQueryBuilder->getFilterMap();
        }

        foreach ($filterRequest->getFilters() as $name => $filterValue) {
            if (isset($filterMap[$name])) {
                [$fieldName, $fieldType] = $filterMap[$name];
                if (\is_callable($fieldName)) {
                    \call_user_func($fieldName, $filterQueryBuilder, $filterValue);
                } else {
                    $filterQueryBuilder->addFilter($fieldName, $fieldType, $filterValue);
                }
            }
        }
    }
}
