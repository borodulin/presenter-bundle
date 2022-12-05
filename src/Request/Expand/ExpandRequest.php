<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Expand;

class ExpandRequest implements ExpandRequestInterface
{
    private array $expand;

    public function __construct(
        array $expand
    ) {
        $this->expand = $expand;
    }

    public function getExpand(): array
    {
        return $this->expand;
    }
}
