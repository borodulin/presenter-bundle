<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Request
{
    public function __construct(
        public ?string $formClass = null
    ) {
    }
}
