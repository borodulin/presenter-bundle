<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class DataProvider
{
    public function __construct(
        public bool $paginated = true,
        public bool $filtered = true,
        public bool $sorted = true,
        public ?string $group = null,
    ) {
    }
}
