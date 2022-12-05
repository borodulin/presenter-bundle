<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Filter;

interface FilterRequestInterface
{
    public function getFilters(): array;
}
