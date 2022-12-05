<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderPaginationInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationResponseInterface;

interface PaginatedInterface extends DataProviderInterface
{
    public function paginate(
        PaginationRequestInterface $request,
        QueryBuilderPaginationInterface $queryBuilder,
        callable $presenter
    ): PaginationResponseInterface;
}
