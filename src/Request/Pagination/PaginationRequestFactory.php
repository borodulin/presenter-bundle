<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Pagination;

use Symfony\Component\HttpFoundation\InputBag;

class PaginationRequestFactory
{
    public function __construct(
        private readonly string $pageKey,
        private readonly string $pageSizeKey,
        private readonly int $defaultPageSize,
        private readonly int $pageStart
    ) {
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
