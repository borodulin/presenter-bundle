<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Sort;

interface SortRequestInterface
{
    public function getSortOrders(): array;
}
