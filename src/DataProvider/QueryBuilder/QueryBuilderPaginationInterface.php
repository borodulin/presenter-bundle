<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider\QueryBuilder;

interface QueryBuilderPaginationInterface
{
    public function setLimit(int $limit): void;

    public function setOffset(int $offset): void;

    public function queryCount(): int;

    public function fetchAll(): array;
}
