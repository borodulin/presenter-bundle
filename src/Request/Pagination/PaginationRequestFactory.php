<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

use Symfony\Component\HttpFoundation\InputBag;

class PaginationRequestFactory
{
    private string $pageKey;
    private string $pageSizeKey;
    private int $defaultPageSize;
    private int $pageStart;

    public function __construct(
        string $pageKey,
        string $pageSizeKey,
        int $defaultPageSize,
        int $pageStart
    ) {
        $this->pageKey = $pageKey;
        $this->pageSizeKey = $pageSizeKey;
        $this->defaultPageSize = $defaultPageSize;
        $this->pageStart = $pageStart;
    }

    public function createFromInputBug(InputBag $inputBag): PaginationRequestInterface
    {
        return new PaginationRequest(
            $this->getIntegerQueryParam($inputBag, $this->pageKey, $this->pageStart),
            $this->getIntegerQueryParam($inputBag, $this->pageSizeKey, $this->defaultPageSize),
            $this->pageStart
        );
    }

    public function createDefault(): PaginationRequestInterface
    {
        return new PaginationRequest($this->pageStart, $this->defaultPageSize, $this->pageStart);
    }

    private function getIntegerQueryParam(InputBag $query, string $name, int $default): int
    {
        $value = $query->get($name);
        if ($value && is_numeric($value)) {
            $value = (int) $value;

            return $value <= 0 ? $default : $value;
        }

        return $default;
    }
}
