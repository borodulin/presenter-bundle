<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\Presenter;

interface PresenterInterface
{
    public function show(object $object, mixed $context): self;
}
