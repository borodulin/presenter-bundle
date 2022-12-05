<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider\QueryBuilder;

interface QueryBuilderFilterInterface
{
    public function getFilterMap(): array;

    public function addFilter(string $fieldName, ?string $fieldType, $filterValue): void;
}
