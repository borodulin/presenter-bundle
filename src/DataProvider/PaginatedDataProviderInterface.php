<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderPaginationInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationRequestInterface;
use Borodulin\PresenterBundle\Request\Pagination\PaginationResponseInterface;

interface PaginatedDataProviderInterface extends DataProviderInterface
{
}
