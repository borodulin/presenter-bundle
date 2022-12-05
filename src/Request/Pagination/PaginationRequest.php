<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

class PaginationRequest implements PaginationRequestInterface
{
    private int $page;
    private int $pageSize;
    private int $pageStart;

    public function __construct(
        int $page,
        int $pageSize,
        int $pageStart
    ) {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->pageStart = $pageStart;
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
