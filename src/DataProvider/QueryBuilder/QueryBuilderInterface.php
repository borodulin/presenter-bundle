<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider\QueryBuilder;

interface QueryBuilderInterface extends QueryBuilderFilterInterface, QueryBuilderPaginationInterface, QueryBuilderSortInterface
{
}
