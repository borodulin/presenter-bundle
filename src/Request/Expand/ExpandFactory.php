<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Expand;

use Symfony\Component\HttpFoundation\InputBag;

class ExpandFactory
{
    private string $expandKey;

    public function __construct(string $expandKey)
    {
        $this->expandKey = $expandKey;
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
}
