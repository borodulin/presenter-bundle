<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsPresenterHandler
{
    public function __construct(
        public ?string $handles = null,
        public ?string $method = null,
        public ?string $presenter = null,
    ) {
    }
}
