<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Filter;

use Symfony\Component\HttpFoundation\InputBag;

class FilterFactory
{
    private array $ignored;

    public function __construct(array $ignored)
    {
        $this->ignored = $ignored;
    }

    public function tryCreateFromInputBug(InputBag $inputBag): ?FilterRequest
    {
        $filters = $this->getFilterQueryParams($inputBag, $this->ignored);
        if ($filters) {
            return new FilterRequest($filters);
        }

        return null;
    }

    private function getFilterQueryParams(InputBag $inputBag, array $ignored): array
    {
        $result = [];
        foreach ($inputBag->all() as $key => $value) {
            if ('' !== $value && !\in_array($key, $ignored)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
