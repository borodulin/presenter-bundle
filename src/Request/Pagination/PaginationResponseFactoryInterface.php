<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

interface PaginationResponseFactoryInterface
{
    public function createResponse(
        int $totalCount,
        int $page,
        int $pageCount,
        int $pageSize,
        array $items
    ): PaginationResponseInterface;
}
