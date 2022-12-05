<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\DataProvider;

use Borodulin\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderInterface;

interface DataProviderInterface
{
    public function getQueryBuilder(): QueryBuilderInterface;
}
