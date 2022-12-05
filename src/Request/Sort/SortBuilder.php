<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Sort;

use Borodulin\PresenterBundle\DataProvider\CustomSortInterface;
use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderSortInterface;

class SortBuilder
{
    public function sort(
        SortRequestInterface $sortRequest,
        QueryBuilderSortInterface $sortQueryBuilder,
        ?CustomSortInterface $customSort
    ): void {
        $sortMap = (null !== $customSort) ? $customSort->getSortFields() : $sortQueryBuilder->getSortMap();

        $orderByArray = [];

        foreach ($sortRequest->getSortOrders() as $name => $sortOrder) {
            if (isset($sortMap[$name])) {
                $orderByArray[$sortMap[$name]] = $sortOrder;
            }
        }
        if (\count($orderByArray)) {
            $sortQueryBuilder->resetOrder();
            foreach ($orderByArray as $sort => $order) {
                $sortQueryBuilder->addOrderBy($sort, $order);
            }
        }
    }
}
