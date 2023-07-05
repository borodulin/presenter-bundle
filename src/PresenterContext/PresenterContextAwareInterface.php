<?php

declare(strict_types=1);

namespace Borodulin\PresenterBundle\PresenterContext;

interface PresenterContextAwareInterface
{
    public function getContext(): ?PresenterContextInterface;

    public function setContext(?PresenterContextInterface $context): void;
}
