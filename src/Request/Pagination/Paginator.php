<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderPaginationInterface;

class Paginator
{
    private int $pageStart;

    public function __construct(
        int $pageStart = 0
    ) {
        $this->pageStart = $pageStart;
    }

    public function paginate(
        PaginationRequestInterface $paginationRequest,
        QueryBuilderPaginationInterface $queryBuilderPagination,
        callable $converter = null
    ): PaginationResponseInterface {
        $pageSize = $paginationRequest->getPageSize();
        $page = $paginationRequest->getPage();
        $offset = ($page - $this->pageStart) * $pageSize;
        $limit = $pageSize;

        $queryBuilderPagination->setLimit($limit);
        $queryBuilderPagination->setOffset($offset);

        $items = $queryBuilderPagination->fetchAll();

        if (null !== $converter) {
            $items = array_map($converter, $items);
        }
        $totalCount = $queryBuilderPagination->queryCount();

        $pageCount = (int) ceil($totalCount / $pageSize);

        return new PaginationResponse(
            $totalCount,
            $page,
            $pageCount,
            $pageSize,
            $items
        );
    }
}
