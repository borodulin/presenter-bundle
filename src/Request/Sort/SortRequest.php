<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Sort;

class SortRequest implements SortRequestInterface
{
    private array $sortOrders;

    public function __construct(
        array $sortOrders
    ) {
        $this->sortOrders = $sortOrders;
    }

    public function getSortOrders(): array
    {
        return $this->sortOrders;
    }
}
