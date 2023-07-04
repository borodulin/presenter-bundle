<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Expand;

use Symfony\Component\HttpFoundation\InputBag;

class ExpandFactory
{
    public function __construct(
        private readonly string $expandKey
    ) {
    }

    public function tryCreateFromInputBug(InputBag $inputBag): ?ExpandRequest
    {
        if ($inputBag->has($this->expandKey)) {
            $expand = array_filter(array_map('trim', explode(
                ',',
                (string) $inputBag->get($this->expandKey)
            )));
            if ($expand) {
                return new ExpandRequest($expand);
            }
        }

        return null;
    }

    public function createFromArray(array $expand): ExpandRequest
    {
        return new ExpandRequest($expand);
    }
}
