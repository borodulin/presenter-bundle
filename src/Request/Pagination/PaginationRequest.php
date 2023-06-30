<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

class PaginationRequest implements PaginationRequestInterface
{
    public function __construct(
        private readonly int $page,
        private readonly int $pageSize,
        private readonly int $pageStart
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getPageStart(): int
    {
        return $this->pageStart;
    }
}
