<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderPaginationInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationResponseInterface;
use Borodulin\PresenterBundle\Request\Pagination\Paginator;

trait PaginatedTrait
{
    public function paginate(
        PaginationRequestInterface $request,
        QueryBuilderPaginationInterface $queryBuilder,
        callable $presenter
    ): PaginationResponseInterface {
        return (new Paginator())
            ->paginate(
                $request,
                $queryBuilder,
                $presenter
            );
    }
}
