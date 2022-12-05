<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider\QueryBuilder;

interface QueryBuilderSortInterface
{
    public function getSortMap(): array;

    public function addOrderBy(string $sort, string $order);

    public function resetOrder(): void;
}
