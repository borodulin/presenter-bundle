<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Sort;

class SortRequest implements SortRequestInterface
{
    public function __construct(
        private readonly array $sortOrders
    ) {
    }

    public function getSortOrders(): array
    {
        return $this->sortOrders;
    }
}
