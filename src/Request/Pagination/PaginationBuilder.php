<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderPaginationInterface;

class PaginationBuilder
{
    public function paginate(
        PaginationRequestInterface $paginationRequest,
        QueryBuilderPaginationInterface $queryBuilderPagination,
        callable $converter = null,
        PaginationResponseFactoryInterface $responseFactory = null
    ): PaginationResponseInterface {
        $pageSize = $paginationRequest->getPageSize();
        $page = $paginationRequest->getPage();
        $pageStart = $paginationRequest->getPageStart();

        $offset = ($page - $pageStart) * $pageSize;
        $limit = $pageSize;

        $queryBuilderPagination->setLimit($limit);
        $queryBuilderPagination->setOffset($offset);

        $items = $queryBuilderPagination->fetchAll();

        if (null !== $converter) {
            $items = array_map($converter, $items);
        }
        $totalCount = $queryBuilderPagination->queryCount();

        $pageCount = (int) ceil($totalCount / $pageSize);

        if (null === $responseFactory) {
            return new PaginationResponse(
                $totalCount,
                $page,
                $pageCount,
                $pageSize,
                $items
            );
        } else {
            return $responseFactory->createResponse(
                $totalCount,
                $page,
                $pageCount,
                $pageSize,
                $items
            );
        }
    }
}
