<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Sort;

use Symfony\Component\HttpFoundation\InputBag;

class SortFactory
{
    public function __construct(
        private readonly string $sortKey
    ) {
    }

    public function tryCreateFromInputBug(InputBag $inputBag): ?SortRequest
    {
        $sortQuery = $inputBag->get($this->sortKey);
        if ($sortQuery) {
            $sortOrders = $this->getSortOrders((string) $sortQuery);
            if ($sortOrders) {
                return new SortRequest($sortOrders);
            }
        }

        return null;
    }

    private function getSortOrders(string $sortQuery): array
    {
        $sortOrders = array_filter(array_map('trim', explode(',', $sortQuery)));
        $result = [];
        foreach ($sortOrders as $sortOrder) {
            $sortOrder = strtolower($sortOrder);
            if (preg_match('/^([+-])?([\w.]+)$/', $sortOrder, $matches)) {
                if (isset($matches[1]) && ('-' === $matches[1])) {
                    $result[$matches[2]] = 'DESC';
                } else {
                    $result[$matches[2]] = 'ASC';
                }
            }
        }

        return $result;
    }
}
