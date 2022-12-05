<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Request\Expand;

interface ExpandRequestInterface
{
    public function getExpand(): array;
}
