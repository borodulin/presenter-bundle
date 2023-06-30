<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

class PaginationResponse implements PaginationResponseInterface
{
    public function __construct(
        private readonly int $totalCount,
        private readonly int $page,
        private readonly int $pageCount,
        private readonly int $pageSize,
        private readonly array $items
    ) {
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getItems(): array
    {
        return $this->items;
    }
}
